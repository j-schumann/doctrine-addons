<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Vrok\DoctrineAddons\Tests\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Gedmo\Sluggable\SluggableListener;
use Vrok\DoctrineAddons\Tests\DBAL\AbstractDbalTestCase;
use Vrok\DoctrineAddons\Tests\Fixtures\SlugEntity;
use Vrok\DoctrineAddons\Tests\Fixtures\TestEntity;
use Vrok\DoctrineAddons\Util\UmlautTransliterator;

abstract class AbstractOrmTestCase extends AbstractDbalTestCase
{
    protected Configuration $configuration;
    protected EntityManager $em;

    protected function setUp(): void
    {
        parent::setUp();

        $configuration = ORMSetup::createAttributeMetadataConfig(
            [__DIR__.'/Fixtures'],
            true
        );
        $configuration->enableNativeLazyObjects(true);
        $this->configuration = $configuration;
    }

    /**
     * Uses the database given in DATABASE_URL, see AbstractDbalTestCase.
     */
    protected function buildEntityManager(): EntityManager
    {
        $conn = DriverManager::getConnection(self::getConnectionParams(), $this->configuration);

        // Event manager
        $evm = new EventManager();

        // Set up the Sluggable listener to test the UmlautTransliterator in
        // combination with Gedmo's Urlizer.
        $sluggableListener = new SluggableListener();
        $sluggableListener->setTransliterator(
            UmlautTransliterator::transliterate(...)
        );

        $evm->addEventSubscriber($sluggableListener);

        $this->em = new EntityManager($conn, $this->configuration, $evm);

        return $this->em;
    }

    protected function setupSchema(): void
    {
        if (!isset($this->em)) {
            $this->buildEntityManager();
        }

        $tool = new SchemaTool($this->em);
        $classes = [
            $this->em->getClassMetadata(TestEntity::class),
            $this->em->getClassMetadata(SlugEntity::class),
        ];
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }
}
