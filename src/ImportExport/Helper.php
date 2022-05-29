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
    protected array $exportableProperties = [];
    protected array $importableProperties = [];
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

            // simply set standard properties & already instantiated objects
            if ($property->getType()->isBuiltin()
                || null === $data[$propName]
                || $data[$propName] instanceof $propType
            ) {
                $value = $data[$propName];
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
                    if (!is_object($element) && !is_array($element)) {
                        // @todo implement byReference
                        throw new \RuntimeException('Collections can only be populated with objects or arrays that will be transformed!');
                    }
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

    protected function getImportableProperties(string $className): array
    {
        $reflection = new ReflectionClass($className);

        if (!array_key_exists($className, $this->importableProperties)) {
            $this->importableProperties[$className] = [];

            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                if ($this->isPropertyImportable($property)) {
                    $this->importableProperties[$className][] = $property;
                }
            }
        }

        return $this->importableProperties[$className];
    }

    protected function getExportableProperties(string $className): array
    {
        $reflection = new ReflectionClass($className);

        if (!array_key_exists($className, $this->exportableProperties)) {
            $this->exportableProperties[$className] = [];

            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                if ($this->isPropertyExportable($property)) {
                    $this->exportableProperties[$className][] = $property;
                }
            }
        }

        return $this->exportableProperties[$className];
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
        $reflection = new ReflectionClass($className);
        $importable = $reflection->getAttributes(ImportableEntity::class);

        return count($importable) > 0;
    }

    protected function isExportableEntity(string $className): bool
    {
        $reflection = new ReflectionClass($className);
        $importable = $reflection->getAttributes(ExportableEntity::class);

        return count($importable) > 0;
    }
}
