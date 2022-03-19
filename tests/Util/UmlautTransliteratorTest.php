<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\Util\UmlautTransliterator;

class UmlautTransliteratorTest extends TestCase
{
    public function testTransliterate(): void
    {
        self::assertSame('', UmlautTransliterator::transliterate(''));
        self::assertSame('', UmlautTransliterator::transliterate(null));
        self::assertSame('', UmlautTransliterator::transliterate(' '));
        self::assertSame('de-de', UmlautTransliterator::transliterate('de_DE'));
        self::assertSame('fr-fr-aeueoe', UmlautTransliterator::transliterate('FR-fr.äüö'));
        self::assertSame('iss-ea-ea', UmlautTransliterator::transliterate(' ìß,èà éá'));
    }
}
