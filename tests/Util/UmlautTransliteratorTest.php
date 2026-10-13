<?php

namespace Vrok\DoctrineAddons\Tests\Util;

use Doctrine\Common\EventManager;
use Gedmo\Sluggable\SluggableListener;
use Vrok\DoctrineAddons\Tests\Fixtures\SlugEntity;
use Vrok\DoctrineAddons\Tests\ORM\AbstractOrmTestCase;
use Vrok\DoctrineAddons\Util\UmlautTransliterator;

final class UmlautTransliteratorTest extends AbstractOrmTestCase
{
    public function testTransliterate(): void
    {
        // only transliterates, whitespace and separators are left to Gedmo's urlizer
        self::assertSame('', UmlautTransliterator::transliterate(''));
        self::assertSame('', UmlautTransliterator::transliterate(null));
        self::assertSame(' ', UmlautTransliterator::transliterate(' '));
        self::assertSame('de_DE', UmlautTransliterator::transliterate('de_DE'));
        self::assertSame('FR-fr.aeueoe', UmlautTransliterator::transliterate('FR-fr.äüö'));
        self::assertSame(' iss,ea ea', UmlautTransliterator::transliterate(' ìß,èà éá'));
    }

    public function testTransliterateWithSluggable(): void
    {
        $sluggableListener = new SluggableListener();
        $sluggableListener->setTransliterator(UmlautTransliterator::transliterate(...));
        $eventManager = new EventManager();
        $eventManager->addEventSubscriber($sluggableListener);

        $em = $this->buildEntityManager($eventManager);
        $this->setupSchema(SlugEntity::class);

        $record = new SlugEntity();
        $record->title = 'A æ Übérmensch på høyeste nivå! И я люблю PHP! есть. ﬁ 北京 ⁴₇';

        $em->persist($record);
        $em->flush();

        self::assertSame(
            'a-ae-uebermensch-pa-hoyeste-niva-i-ya-lyublyu-php-yest-fi-bei-jing-47',
            $record->slug
        );
    }
}
