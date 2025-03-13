<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\ImportExport;

use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Helper to convert arrays to (Doctrine) entities and export entities as arrays.
 * Uses Reflection to determine property types and check for properties tagged
 * with #[ImportableProperty] or #[ExportableProperty] and entity classes tagged
 * with #[ImportableEntity].
 * Uses Symfony's PropertyAccess to get/set properties using the correct
 * getters/setters (which also supports hassers and issers).
 */
class Helper
{
    // static caches to reduce Reflection calls when im-/exporting multiple
    // objects of the same class
    protected static array $typeDetails = [];
    protected static array $exportableEntities = [];
    protected static array $importableEntities = [];
    protected static array $exportableProperties = [];
    protected static array $importableProperties = [];

    protected PropertyAccessorInterface $propertyAccessor;

    protected ?ObjectManager $objectManager = null;

    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
    }

    public function setObjectManager(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates an instance of the given entityClass and populates it with the
     * data given as array.
     * Alternatively the entityClass can be given as additional array element
     * with index _entityClass.
     * Can recurse over properties that themselves are entities or collections
     * of entities.
     * Also instantiates Datetime[Immutable] properties from strings.
     * To determine which properties to populate the attribute
     * #[ImportableProperty] is used. Can infer the entityClass from the
     * property's type for classes that are tagged with #[ImportableEntity].
     *
     * @throws \JsonException|\ReflectionException
     */
    public function fromArray(array $data, ?string $entityClass = null): object
    {
        // let the defined _entityClass take precedence over the (possibly
        // inferred) $entityClass from a property type, which may be an abstract
        // superclass or an interface
        $className = $data['_entityClass'] ?? $entityClass;

        if (empty($className)) {
            $encoded = json_encode($data, \JSON_THROW_ON_ERROR);
            throw new \RuntimeException("No entityClass given to instantiate the data: $encoded");
        }

        if (interface_exists($className)) {
            throw new \RuntimeException("Cannot create instance of the interface $className, concrete class needed!");
        }

        if (!class_exists($className)) {
            throw new \RuntimeException("Class $className does not exist!");
        }

        if ($entityClass && isset($data['_entityClass'])
            && !is_a($data['_entityClass'], $entityClass, true)
        ) {
            throw new \RuntimeException("Given '_entityClass' {$data['_entityClass']} is not a subclass/implementation of $entityClass!");
        }

        $classReflection = new \ReflectionClass($className);
        if ($classReflection->isAbstract()) {
            throw new \RuntimeException("Cannot create instance of the abstract class $className, concrete class needed!");
        }

        $instance = new $className();

        foreach ($this->getImportableProperties($className) as $property) {
            $propName = $property->getName();
            if (!\array_key_exists($propName, $data)) {
                continue;
            }

            $value = null;
            $typeDetails = $this->getTypeDetails($className, $property);
            $importAttrib = $property->getAttributes(ImportableProperty::class)[0];
            $listOf = $importAttrib->getArguments()['listOf'] ?? null;

            if (null === $data[$propName]) {
                if (!$typeDetails['allowsNull']) {
                    throw new \RuntimeException("Found NULL for $className::$propName, but property is not nullable!");
                }

                $value = null;
            } elseif ($typeDetails['isBuiltin']) {
                // check for listOf, the property could be an array of DTOs etc.
                $value = $listOf
                    ? $this->processList($data[$propName], $property, $listOf)
                    // simply set standard properties, the propertyAccessor will throw
                    // an exception if the types don't match.
                    : $data[$propName];
            } elseif (\is_object($data[$propName])) {
                // set already instantiated objects, we cannot modify/convert those,
                // and the may have different classes, e.g. when the type is a union.
                // If the object type is not allowed the propertyAccessor will throw
                // an exception.
                $value = $data[$propName];
            } elseif (\is_array($data[$propName]) && !$typeDetails['classname']) {
                // We have an array but no type information -> the target property
                // could be a unionType that allows multiple classes or it could
                // be untyped. So if the importer expects us to create an instance
                // ('_entityClass' is set) try to create & set it, else use the
                // value as is.
                $value = isset($data[$propName]['_entityClass'])
                    ? $this->fromArray($data[$propName])
                    : $data[$propName];
            } elseif (!$typeDetails['classname']) {
                // if we are this deep in the IFs it means the data is no array and this
                // is a uniontype with no classes (e.g. int|string) -> let the
                // propertyAccessor try to set the value as is.
                $value = $data[$propName];
            } elseif ($this->isImportableEntity($typeDetails['classname'])) {
                if (\is_int($data[$propName]) || \is_string($data[$propName])) {
                    if (null === $this->objectManager) {
                        throw new \RuntimeException("Found ID for $className::$propName, but objectManager is not set to find object!");
                    }

                    $value = $this->objectManager->find(
                        $typeDetails['classname'],
                        $data[$propName]
                    );
                } else {
                    $value = $this->fromArray($data[$propName], $typeDetails['classname']);
                }
            } elseif (is_a($typeDetails['classname'], Collection::class, true)) {
                // @todo We simply assume here that
                // a) the collection members are importable
                // -> use Doctrine Schema data to determine the collection type
                // c) the collection can be set as array at once
                $value = [];
                foreach ($data[$propName] as $element) {
                    if (!\is_array($element) && !\is_object($element)) {
                        throw new \RuntimeException("Elements imported for $className::$propName should be either an object or an array!");
                    }

                    $value[] = \is_object($element)
                        // use objects directly...
                        ? $element
                        // ... or try to create, if listOf is not set than each
                        // element must contain an _entityClass
                        : $this->fromArray($element, $listOf);
                }
            } elseif (is_a($typeDetails['classname'], \DateTimeInterface::class, true)) {
                $value = new ($typeDetails['classname'])($data[$propName]);
            } else {
                throw new \RuntimeException("Don't know how to import '$property' for $className!");
            }

            $this->propertyAccessor->setValue(
                $instance,
                $propName,
                $value
            );
        }

        return $instance;
    }

    // @todo: catch union types w/ multiple builtin types
    protected function getTypeDetails(string $classname, \ReflectionProperty $property): array
    {
        $propName = $property->getName();
        if (isset(self::$typeDetails["$classname::$propName"])) {
            return self::$typeDetails["$classname::$propName"];
        }

        $type = $property->getType();
        $data = [
            'allowsArray' => null === $type, // untyped allows arrays of course
            'allowsNull'  => $type?->allowsNull() ?? true, // also works for union types
            'classname'   => null,
            'typename'    => null,
            'isBuiltin'   => false,
            'isUnion'     => $type instanceof \ReflectionUnionType,
        ];

        if (null === $type) {
            self::$typeDetails["$classname::$propName"] = $data;

            return $data;
        }

        if ($data['isUnion']) {
            foreach ($type->getTypes() as $unionVariant) {
                /** @var \ReflectionNamedType $unionVariant */
                $variantName = $unionVariant->getName();
                if ('array' === $variantName) {
                    $data['allowsArray'] = true;
                    continue;
                }

                if (!$unionVariant->isBuiltin()) {
                    if (null !== $data['classname']) {
                        // @todo Improve this. We could store a list of classnames
                        // to check against in fromArray()
                        throw new \RuntimeException("Cannot import object, found ambiguous union type: $type");
                    }

                    $data['classname'] = $variantName;
                }
            }
        } elseif ($type->isBuiltin()) {
            $data['isBuiltin'] = true;
            $data['allowsNull'] = $type->allowsNull();
            $data['typename'] = $type->getName();
            if ('array' === $data['typename']) {
                $data['allowsArray'] = true;
            }
        } else {
            $propClass = $type->getName();
            $data['classname'] = 'self' === $propClass ? $classname : $propClass;
        }

        self::$typeDetails["$classname::$propName"] = $data;

        return $data;
    }

    /**
     * @throws \JsonException|\RuntimeException|\ReflectionException
     */
    protected function processList(mixed $list, \ReflectionProperty $property, string $listOf): array
    {
        if (null === $list) {
            return [];
        }

        if (!$this->isImportableEntity($listOf)) {
            throw new \LogicException("Property $property->class::$property->name is marked with ImportableProperty but its given listOf '$listOf' is no ImportableEntity!");
        }

        if (!\is_array($list)) {
            $json = json_encode($list, \JSON_THROW_ON_ERROR);
            throw new \RuntimeException("Property $property->class::$property->name is marked as list of '$listOf' but it is no array: $json!");
        }

        foreach ($list as $key => $entry) {
            if (!\is_array($entry)) {
                $json = json_encode($entry, \JSON_THROW_ON_ERROR);
                throw new \RuntimeException("Property $property->class::$property->name is marked as list of '$listOf' but entry is no array: $json!");
            }

            $list[$key] = $this->fromArray($entry, $listOf);
        }

        return $list;
    }

    /**
     * Converts the given (Doctrine) entity to an array, converting referenced entities
     * and collections to arrays too. Datetime instances are returned as ATOM strings.
     * Exports only properties that are marked with #[ExportableProperty]. If a reference
     * uses the referenceByIdentifier argument in the attribute the value of.
     *
     * @param object     $object         the entity to export, must be tagged with #[ExportableEntity]
     * @param array|null $propertyFilter if an array: only properties with the given names
     *                                   are returned
     *
     * @throws \ReflectionException
     */
    public function toArray(object $object, ?array $propertyFilter = null): array
    {
        $className = $object::class;
        if (!$this->isExportableEntity($className)) {
            throw new \RuntimeException("Don't know how to export instance of $className!");
        }

        $data = [];
        /** @var \ReflectionProperty $property */
        foreach ($this->getExportableProperties($className) as $property) {
            $propName = $property->getName();
            if (null !== $propertyFilter && !\in_array($propName, $propertyFilter, true)) {
                continue;
            }

            $propValue = $this->propertyAccessor->getValue($object, $propName);
            $exportAttrib = $property->getAttributes(ExportableProperty::class)[0];
            $referenceByIdentifier = $exportAttrib->getArguments()['referenceByIdentifier'] ?? null;

            if (null === $propValue) {
                $data[$propName] = null;
            } elseif ($propValue instanceof \DateTimeInterface) {
                $data[$propName] = $propValue->format(\DATE_ATOM);
            } elseif ($propValue instanceof Collection) {
                $data[$propName] = [];
                foreach ($propValue as $element) {
                    if (null !== $referenceByIdentifier) {
                        $identifier = $this->toArray($element, (array) $referenceByIdentifier);
                        $data[$propName][] = $identifier[$referenceByIdentifier];
                    } else {
                        $elementData = $this->toArray($element);
                        $elementData['_entityClass'] = $element::class;
                        $data[$propName][] = $elementData;
                    }
                }
            } elseif (\is_object($propValue) && $this->isExportableEntity($propValue::class)) {
                if (null !== $referenceByIdentifier) {
                    $identifier = $this->toArray($propValue, (array) $referenceByIdentifier);
                    $data[$propName] = $identifier[$referenceByIdentifier];
                } else {
                    $data[$propName] = $this->toArray($propValue);
                    if ($propValue::class !== $property->class) {
                        $data[$propName]['_entityClass'] = $propValue::class;
                    }
                }
            } elseif (\is_array($propValue)) {
                // @todo hacky solution. Maybe merge with collection handling
                // or determine if nested export is intended by another option
                // on the exportAttrib, or if the importAttrib has "listOf"
                $data[$propName] = [];
                foreach ($propValue as $key => $element) {
                    if (\is_object($element)) {
                        if (null !== $referenceByIdentifier) {
                            $identifier = $this->toArray($element, (array) $referenceByIdentifier);
                            $data[$propName][$key] = $identifier[$referenceByIdentifier];
                        } elseif ($this->isExportableEntity($element::class)) {
                            $elementData = $this->toArray($element);
                            $elementData['_entityClass'] = $element::class;
                            $data[$propName][$key] = $elementData;
                        } else {
                            // any other object is kept as-is
                            $data[$propName][$key] = $element;
                        }
                    } else {
                        // any other type is kept as-is
                        $data[$propName][$key] = $element;
                    }
                }
            } elseif (\is_int($propValue) || \is_float($propValue) || \is_bool($propValue) || \is_string($propValue)) {
                $data[$propName] = $propValue;
            } else {
                throw new \RuntimeException("Don't know how to export $className::$propName!");
            }
        }

        return $data;
    }

    /**
     * We use a static cache here as the properties of classes won't change
     * while the PHP instance is running and this method could be called
     * multiple times, e.g. when importing many objects of the same class.
     *
     * @throws \ReflectionException
     */
    protected function getImportableProperties(string $className): array
    {
        if (!isset(self::$importableProperties[$className])) {
            $reflection = new \ReflectionClass($className);
            self::$importableProperties[$className] = [];

            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                if ($this->isPropertyImportable($property)) {
                    self::$importableProperties[$className][] = $property;
                }
            }
        }

        return self::$importableProperties[$className];
    }

    /**
     * We use a static cache here as the properties of classes won't change
     * while the PHP instance is running and this method could be called
     * multiple times, e.g. when exporting many objects of the same class.
     *
     * @throws \ReflectionException
     */
    protected function getExportableProperties(string $className): array
    {
        if (!isset(self::$exportableProperties[$className])) {
            $reflection = new \ReflectionClass($className);
            self::$exportableProperties[$className] = [];

            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                if ($this->isPropertyExportable($property)) {
                    self::$exportableProperties[$className][] = $property;
                }
            }
        }

        return self::$exportableProperties[$className];
    }

    protected function isPropertyExportable(\ReflectionProperty $property): bool
    {
        return [] !== $property->getAttributes(ExportableProperty::class);
    }

    protected function isPropertyImportable(\ReflectionProperty $property): bool
    {
        return [] !== $property->getAttributes(ImportableProperty::class);
    }

    /**
     * @throws \ReflectionException
     */
    protected function isImportableEntity(string $className): bool
    {
        if (!isset(self::$importableEntities[$className])) {
            $reflection = new \ReflectionClass($className);
            $importable = $reflection->getAttributes(ImportableEntity::class);
            self::$importableEntities[$className] = [] !== $importable;
        }

        return self::$importableEntities[$className];
    }

    /**
     * @throws \ReflectionException
     */
    protected function isExportableEntity(string $className): bool
    {
        if (!isset(self::$exportableEntities[$className])) {
            $reflection = new \ReflectionClass($className);
            $exportable = $reflection->getAttributes(ExportableEntity::class);
            self::$exportableEntities[$className] = [] !== $exportable;
        }

        return self::$exportableEntities[$className];
    }
}
