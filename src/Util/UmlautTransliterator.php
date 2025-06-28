<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Util;

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
        // The default behavior of abandoned Behat\Transliterator and PHP's
        // internal Transliterator would be to replace umlauts with "a, o, u"
        // instead of "ae, oe, ue", but the latter improves readability in
        // German a lot.
        // PHP's Transliterator would keep subscript/superscript numbers
        // untouched, which would cause Sluggable to replace them by a dash, so
        // we manually convert them to normal digits.
        // Also, "Russian-Latin/BGN" improves readability, as it returns
        // "i ya lyublyu php" instead of "i a lublu php" for "И я люблю PHP".
        $rules = '
                ä > ae ;
                ö > oe ;
                ü > ue ;
                Ä > Ae ;
                Ö > Oe ;
                Ü > Ue ;
                ß > ss ;

                ⁰ > 0 ;
                ¹ > 1 ;
                ² > 2 ;
                ³ > 3 ;
                ⁴ > 4 ;
                ⁵ > 5 ;
                ⁶ > 6 ;
                ⁷ > 7 ;
                ⁸ > 8 ;
                ⁹ > 9 ;

                ₀ > 0 ;
                ₁ > 1 ;
                ₂ > 2 ;
                ₃ > 3 ;
                ₄ > 4 ;
                ₅ > 5 ;
                ₆ > 6 ;
                ₇ > 7 ;
                ₈ > 8 ;
                ₉ > 9 ;

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
