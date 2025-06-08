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

        // Opinionated implementation:
        // The default behavior of Behat\Transliterator and PHP's internal
        // Transliterator would be to replace umlauts with "a, o, u" instead
        // of "ae, oe, ue", but the latter improves readability in German a lot.
        // Also Russian-Latin/BGN improves readability, as it returns
        // "i ya lyublyu php" instead of "i a lublu php" for "И я люблю PHP".
        $rules = '
                ä > ae ;
                ö > oe ;
                ü > ue ;
                Ä > Ae ;
                Ö > Oe ;
                Ü > Ue ;
                ß > ss ;
                :: Russian-Latin/BGN;
                :: Any-Latin ;
                :: Latin-ASCII ;
            ';
        $transliterator = \Transliterator::createFromRules(
            $rules,
            \Transliterator::FORWARD
        );

        return $transliterator->transliterate($text);
    }
}
