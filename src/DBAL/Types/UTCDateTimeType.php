<?php

namespace Vrok\DoctrineAddons\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;

/**
 * Stores a DateTime value in UTC in the database, returns DateTimeImmutable instances.
 */
class UTCDateTimeType extends DateTimeImmutableType
{
    private static ?\DateTimeZone $utcDateTimezone = null;

    /**
     * @throws ConversionException
     */
    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof \DateTimeInterface) {
            throw InvalidType::new($value, static::class, ['DateTime', 'DateTimeImmutable', 'DateTimeInterface']);
        }

        $value = \DateTimeImmutable::createFromInterface($value);
        self::$utcDateTimezone ??= new \DateTimeZone('UTC');

        if ('UTC' !== $value->getTimezone()->getName()) {
            $value = $value->setTimezone(self::$utcDateTimezone);
        }

        return $value->format($platform->getDateTimeFormatString());
    }

    /**
     * @throws ConversionException
     */
    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): ?\DateTimeImmutable
    {
        if (null === $value || $value instanceof \DateTimeImmutable) {
            return $value;
        }

        if (!\is_string($value)) {
            throw InvalidType::new($value, static::class, ['string']);
        }

        self::$utcDateTimezone ??= new \DateTimeZone('UTC');

        $converted = \DateTimeImmutable::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            self::$utcDateTimezone
        );

        if (!$converted) {
            throw InvalidFormat::new($value, static::class, $platform->getDateTimeFormatString());
        }

        return $converted;
    }
}
