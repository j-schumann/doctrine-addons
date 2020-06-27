<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Types;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeImmutableType;

/**
 * Stores a DateTime value in UTC in the database, returnes DateTimeImmutable
 * instances.
 */
class UTCDateTimeType extends DateTimeImmutableType
{
    /**
     * @var \DateTimezone
     */
    private static $utcDateTimezone;

    /**
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return $value;
        }

        if (!($value instanceof DateTimeInterface)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['DateTime', 'DateTimeImmutable', 'DateTimeInterface']);
        }

        if (!($value instanceof DateTimeImmutable)) {
            $value = DateTimeImmutable::createFromMutable($value);
        }

        self::$utcDateTimezone = self::$utcDateTimezone ?: new \DateTimeZone('UTC');

        if ('UTC' !== $value->getTimezone()->getName()) {
            $value = $value->setTimezone(self::$utcDateTimezone);
        }

        return $value->format($platform->getDateTimeFormatString());
    }

    /**
     * @param mixed $value
     *
     * @return DateTimeImmutable|null
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return $value;
        }

        if (!is_string($value)) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['string']);
        }

        self::$utcDateTimezone = self::$utcDateTimezone ?: new DateTimeZone('UTC');

        $converted = DateTimeImmutable::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            self::$utcDateTimezone
        );

        if (!$converted) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString());
        }

        return $converted;
    }
}
