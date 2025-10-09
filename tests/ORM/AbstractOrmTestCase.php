<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Gedmo\Sluggable\SluggableListener;
use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\Tests\Fixtures\ImportEntity;
use Vrok\DoctrineAddons\Tests\Fixtures\SlugEntity;
use Vrok\DoctrineAddons\Tests\Fixtures\TestEntity;
use Vrok\DoctrineAddons\Util\UmlautTransliterator;

abstract class AbstractOrmTestCase extends TestCase
{
    protected Configuration $configuration;
    protected EntityManager $em;

    protected function setUp(): void
    {
        parent::setUp();

        // @todo Replace with createAttributeMetadataConfig when minimum required
        //       doctrine/orm is 3.5.x
        $configuration = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__.'/../Fixtures'],
            true
        );

        // @todo Remove with ORM 4.0:
        $configuration->setProxyDir(sys_get_temp_dir());
        $configuration->setProxyNamespace('Tests\Fixtures\Proxies');
        $configuration->setAutoGenerateProxyClasses(true);

        $this->configuration = $configuration;
    }

    protected function buildEntityManager(): EntityManager
    {
        $conn = DriverManager::getConnection(
            ['driver' => 'pdo_sqlite', 'memory' => true],
            $this->configuration
        );

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
        if (!$this->em) {
            $this->buildEntityManager();
        }

        $tool = new SchemaTool($this->em);
        $classes = [
            $this->em->getClassMetadata(ImportEntity::class),
            $this->em->getClassMetadata(TestEntity::class),
            $this->em->getClassMetadata(SlugEntity::class),
        ];
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }
}
