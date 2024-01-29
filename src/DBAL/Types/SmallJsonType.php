<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;

/**
 * Stores JSON in a string/varchar field instead of LONGTEXT.
 * a) to save space/optimize performance, LONGTEXT is not included in the row
 *    but stored separately
 * b) to allow inclusion of the column in an index/primary key.
 *
 * Use only for small data objects, that produce JSON < 256 characters,
 * e.g. references with ID + classname/tablename.
 */
class SmallJsonType extends StringType
{
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        $encoded = json_encode($value);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw ConversionException::conversionFailedSerialization($value, 'json', json_last_error_msg());
        }

        return $encoded;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        $val = json_decode($value, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return $val;
    }

    public function getName(): string
    {
        return 'small_json';
    }
}
