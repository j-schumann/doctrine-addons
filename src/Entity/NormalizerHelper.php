<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Entity;

abstract class NormalizerHelper
{
    /**
     * Removes HTML tags from the given value and trims the result.
     * Used to check for empty values and the real text length for entity fields
     * that allow HTML.
     */
    public static function stripHtml(?string $content): ?string
    {
        if (null === $content) {
            return null;
        }

        return trim(strip_tags($content));
    }

    public static function getTextLength(?string $content): int
    {
        $normalized = self::stripHtml($content);

        return null === $normalized ? 0 : mb_strlen($normalized);
    }

    /**
     * Checks if the string is empty after stripping tags & trimming,
     * if yes NULL is returned, else the trimmed string including tags.
     * This prevents storing empty markup.
     */
    public static function toHtml(?string $value): string
    {
        return 0 === NormalizerHelper::getTextLength($value) ? '' : trim($value);
    }

    /**
     * Checks if the string is empty after stripping tags & trimming,
     * if yes NULL is returned, else the trimmed string including tags.
     * This prevents storing empty markup.
     */
    public static function toNullableHtml(?string $value): ?string
    {
        return 0 === NormalizerHelper::getTextLength($value) ? null : trim($value);
    }

    /**
     * Checks if the string is empty after trimming,
     * if yes '' is returned, else the trimmed value.
     */
    public static function toString(?string $value): string
    {
        if (null === $value) {
            return '';
        }

        // Only trim, no strip_tags as this would remove "<3" or "11<12" etc.
        // This is no validation, so checking for linebreaks within the string
        // etc. must be done separately.
        return trim($value);
    }

    /**
     * Checks if the string is empty after trimming,
     * if yes NULL is returned, else the trimmed value.
     */
    public static function toNullableString(?string $value)
    {
        if (null === $value) {
            return null;
        }

        // Only trim, no strip_tags as this would remove "<3" or "11<12" etc.
        // This is no validation, so checking for linebreaks within the string
        // etc. must be done separately.
        $sanitized = trim($value);

        // multi-byte not relevant here, don't use empty() or ?: here as a string
        // containing '0' would be seen as empty/falsey
        return '' === $sanitized ? null : $sanitized;
    }

    /**
     * All empty values (after trimming) are removed, the resulting array is returned.
     */
    public static function toStringArray(?array $values): array
    {
        if (null === $values || 0 === count($values)) {
            return [];
        }

        $cleaned = [];
        foreach ($values as $value) {
            if (null === $value) {
                continue;
            }

            if (!is_string($value)) {
                // this method is used in a setter, before validation occurs,
                // keep the invalid value for the type(string) validator.
                $cleaned[] = $value;
                continue;
            }

            // Only trim, no strip_tags as this would remove "<3" or "11<12" etc.
            // This is no validation, so checking for linebreaks within the string
            // etc. must be done separately.
            $value = trim($value);

            // multi-byte not relevant here, don't use empty() or ?: here as strings
            // containing '0' would be seen as empty/falsey
            if ('' !== $value) {
                $cleaned[] = $value;
            }
        }

        return $cleaned;
    }

    /**
     * Checks if the array is empty and contains non-empty entries. All empty
     * values (after trimming) are removed. If non-empty values remain
     * they are returned, else NULL.
     */
    public static function toNullableStringArray(?array $values, bool $keepIndices = false): ?array
    {
        if (null === $values || 0 === count($values)) {
            return null;
        }

        $cleaned = [];
        foreach ($values as $key => $value) {
            if (null === $value) {
                continue;
            }

            if (!is_string($value)) {
                // this method is used in a setter, before validation occurs,
                // keep the invalid value for the type(string) validator.
                $cleaned[$key] = $value;
                continue;
            }

            // Only trim, no strip_tags as this would remove "<3" or "11<12" etc.
            // This is no validation, so checking for linebreaks within the string
            // etc. must be done separately.
            $value = trim($value);

            // multi-byte not relevant here, don't use empty() or ?: here as strings
            // containing '0' would be seen as empty/falsey
            if ('' !== $value) {
                // preserve keys, e.g. for indexes referencing other objects
                $cleaned[$key] = $value;
            }
        }

        $newCount = count($cleaned);
        if (!$keepIndices && $newCount != count($values)) {
            $cleaned = array_values($cleaned);
        }

        return $newCount ? $cleaned : null;
    }

    /**
     * Checks if the array is empty and contains non-empty entries. All empty values
     * are removed. If non-empty values remain they are returned, else NULL.
     */
    public static function toNullableArray(?array $values): ?array
    {
        if (null === $values || 0 === count($values)) {
            return null;
        }

        $cleaned = [];
        foreach ($values as $key => $value) {
            // only check for empty, the type of the values is not specified,
            // this will skip NULL, FALSE, 0, '0', [], ""
            if (!empty($value)) {
                // preserve keys, e.g. for indexes referencing other objects
                $cleaned[$key] = $value;
            }
        }

        return count($cleaned) ? $cleaned : null;
    }

    public static function toNullableFloat(?float $value): ?float
    {
        if (null === $value) {
            return null;
        }

        return 0 == $value ? null : $value;
    }

    public static function toNullableInt(?int $value): ?int
    {
        if (null === $value) {
            return null;
        }

        return 0 == $value ? null : $value;
    }
}
