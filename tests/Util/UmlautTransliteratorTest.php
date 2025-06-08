<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\Util;

use Vrok\DoctrineAddons\Tests\Fixtures\SlugEntity;
use Vrok\DoctrineAddons\Tests\ORM\AbstractOrmTestCase;
use Vrok\DoctrineAddons\Util\UmlautTransliterator;

class UmlautTransliteratorTest extends AbstractOrmTestCase
{
    public function testTransliterate(): void
    {
        // Updated for 2.14.0: The transliterator did not only transliterate but
        // also already slugify the given text (replaced whitespace with the
        // separator and trimmed the value), but this is done in
        // Gedmo/SluggableListener by calling the "urlizer", so the updated
        // implementation behaves more non-intrusive.
        self::assertSame('', UmlautTransliterator::transliterate(''));
        self::assertSame('', UmlautTransliterator::transliterate(null));
        self::assertSame(' ', UmlautTransliterator::transliterate(' '));
        self::assertSame('de_DE', UmlautTransliterator::transliterate('de_DE'));
        self::assertSame('FR-fr.aeueoe', UmlautTransliterator::transliterate('FR-fr.äüö'));
        self::assertSame(' iss,ea ea', UmlautTransliterator::transliterate(' ìß,èà éá'));
    }

    /**
     * This tests the integration of the transliterator (injected in the
     * AbstractOrmTestCase) with Gedmo\Sluggable, to check if umlauts and other
     * special chars are correctly transliterated and then remaining whitespaces
     * are replaced with the separator (defaults to dash).
     */
    public function testSluggable(): void
    {
        $em = $this->buildEntityManager();
        $this->setupSchema();

        $record = new SlugEntity();
        $record->title = 'A æ Übérmensch på høyeste nivå! И я люблю PHP! есть. ﬁ 北京';
        $em->persist($record);
        $em->flush();

        self::assertSame(
            'a-ae-uebermensch-pa-hoyeste-niva-i-ya-lyublyu-php-yest-fi-bei-jing',
            $record->slug
        );
    }
}
