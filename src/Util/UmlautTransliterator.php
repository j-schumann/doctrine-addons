<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Util;

use Behat\Transliterator\Transliterator;

/**
 * Used to customize the Gedmo\Sluggable listener:
 * Transliterate umlauts [ä, ü, ö] to [ae, ue, oe] instead of [a, u, o].
 */
class UmlautTransliterator
{
    public static function transliterate(?string $text, string $separator = '-'): string
    {
        if (null === $text || '' === $text) {
            return '';
        }

        $text = Transliterator::unaccent($text);
        $text = Transliterator::transliterate($text, $separator);

        return $text;
    }
}
