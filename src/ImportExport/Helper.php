<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\ImportExport;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Helper to convert arrays to (Doctrine) entities and export entities as arrays.
 * Uses Reflection to determine property types and check for properties tagged with
 * #[ImportableProperty] or #[ExportableProperty] and entity classes tagged with
 * #[ImportableEntity].
 * Uses Symfony's PropertyAccess to get/set properties using the correct getters/setters
 * (which also supports hassers and issers).
 */
class Helper
{
    // static caches to reduce Reflection calls when im-/exporting multiple
    // objects of the same class
    protected static array $exportableEntities = [];
    protected static array $importableEntities = [];
    protected static array $exportableProperties = [];
    protected static array $importableProperties = [];

    protected PropertyAccessorInterface $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
    }

    /**
     * Creates an instance of the given entityClass and populates it with the data
     * given as array.
     * Alternatively the entityClass can be given as additional array element with
     * index _entityClass.
     * Can recurse over properties that themselves are entities or collections of entities.
     * Also instantiates Datetime[Immutable] properties from strings.
     * To determine which properties to populate the attribute #[ImportableProperty]
     * is used. Can infer the entityClass from the property's type for classes that
     * are tagged with #[ImportableEntity].
     *
     * @throws \JsonException
     */
    public function fromArray(array $data, string $enityClass = null): object
    {
        $className = $data['_entityClass'] ?? $enityClass;

        if (empty($className)) {
            $encoded = json_encode($data, JSON_THROW_ON_ERROR);
            throw new RuntimeException("No entityClass given to instantiate the data: $encoded");
        }

        // let the defined _entityClass take precedence over the (possibly inferred)
        // $enityClass from a property type, which maybe an abstract superclass
        $instance = new $className();

        foreach ($this->getImportableProperties($className) as $property) {
            $propName = $property->getName();
            if (!array_key_exists($propName, $data)) {
                continue;
            }

            $propType = $property->getType()->getName();
            if ('self' === $propType) {
                $propType = $className;
            }

            $value = null;

            $importAttrib = $property->getAttributes(ImportableProperty::class)[0];
            $listOf = $importAttrib->getArguments()['listOf'] ?? null;

            // simply set standard properties & already instantiated objects
            if ($property->getType()->isBuiltin()
                || null === $data[$propName]
                || $data[$propName] instanceof $propType
            ) {
                $value = $listOf
                    ? $this->processList($data[$propName], $property, $listOf)
                    : $data[$propName];
            } elseif ($this->isImportableEntity($propType)) {
                $value = $this->fromArray($data[$propName], $propType);
            } elseif (is_a($propType, Collection::class, true)) {
                // @todo We simply assume here that
                // a) each element contains the _entityClass of the collection members
                // b) the collection members are importable
                // -> use Doctrine Schema data to determine the collection type
                // c) the collection can be set as array at once
                $value = [];
                foreach ($data[$propName] as $element) {
                    $value[] = is_object($element) ? $element : $this->fromArray($element);
                }
            } elseif (is_a($propType, DateTimeInterface::class, true)) {
                $value = new $propType($data[$propName]);
            } else {
                throw new RuntimeException("Don't know how to import '$propType $propName' for $className!");
            }

            $this->propertyAccessor->setValue(
                $instance,
                $propName,
                $value
            );
        }

        return $instance;
    }

    protected function processList(mixed $list, ReflectionProperty $property, string $listOf): array
    {
        if (null === $list) {
            return [];
        }

        if (!$this->isExportableEntity($listOf)) {
            throw new \LogicException("Property {$property->class}::{$property->name} is marked with ImportableProperty but its given listOf '$listOf' is no ImportableEntity!");
        }

        if (!is_array($list)) {
            $json = json_encode($list);
            throw new \RuntimeException("Property {$property->class}::{$property->name} is marked as list of '$listOf' but it is no array: $json!");
        }

        foreach ($list as $key => $entry) {
            if (!is_array($entry)) {
                $json = json_encode($entry);
                throw new \RuntimeException("Property {$property->class}::{$property->name} is marked as list of '$listOf' but entry is no array: $json!");
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
     */
    public function toArray(object $object, ?array $propertyFilter = null): array
    {
        $className = $object::class;
        if (!$this->isExportableEntity($className)) {
            throw new RuntimeException("Don't know how to export instance of $className!");
        }

        $data = [];
        /** @var ReflectionProperty $property */
        foreach ($this->getExportableProperties($className) as $property) {
            $propName = $property->getName();
            if (null !== $propertyFilter && !in_array($propName, $propertyFilter)) {
                continue;
            }

            $propValue = $this->propertyAccessor->getValue($object, $propName);
            $exportAttrib = $property->getAttributes(ExportableProperty::class)[0];
            $referenceByIdentifier = $exportAttrib->getArguments()['referenceByIdentifier'] ?? null;

            if ($property->getType()->isBuiltin()
                || null === $propValue
            ) {
                $data[$propName] = $propValue;
            } elseif ($propValue instanceof DateTimeInterface) {
                /* @var DateTimeInterface $propValue */
                $data[$propName] = $propValue->format(DATE_ATOM);
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
            } elseif (is_object($propValue)) {
                if (null !== $referenceByIdentifier) {
                    $identifier = $this->toArray($propValue, (array) $referenceByIdentifier);
                    $data[$propName] = $identifier[$referenceByIdentifier];
                } else {
                    $data[$propName] = $this->toArray($propValue);
                }
            } else {
                throw new RuntimeException("Don't know how to export $className::$propName!");
            }
        }

        return $data;
    }

    /**
     * We use a static cache here as the properties of classes won't change
     * while the PHP instance is running and this method could be called
     * multiple times, e.g. when importing many objects of the same class.
     */
    protected function getImportableProperties(string $className): array
    {
        if (!isset(self::$importableProperties[$className])) {
            $reflection = new ReflectionClass($className);
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
     */
    protected function getExportableProperties(string $className): array
    {
        if (!isset(self::$exportableProperties[$className])) {
            $reflection = new ReflectionClass($className);
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

    protected function isPropertyExportable(ReflectionProperty $property): bool
    {
        return count($property->getAttributes(ExportableProperty::class)) > 0;
    }

    protected function isPropertyImportable(ReflectionProperty $property): bool
    {
        return count($property->getAttributes(ImportableProperty::class)) > 0;
    }

    protected function isImportableEntity(string $className): bool
    {
        if (!isset(self::$importableEntities[$className])) {
            $reflection = new ReflectionClass($className);
            $importable = $reflection->getAttributes(ImportableEntity::class);
            self::$importableEntities[$className] = count($importable) > 0;
        }

        return self::$importableEntities[$className];
    }

    protected function isExportableEntity(string $className): bool
    {
        if (!isset(self::$exportableEntities[$className])) {
            $reflection = new ReflectionClass($className);
            $exportable = $reflection->getAttributes(ExportableEntity::class);
            self::$exportableEntities[$className] = count($exportable) > 0;
        }

        return self::$exportableEntities[$className];
    }
}
