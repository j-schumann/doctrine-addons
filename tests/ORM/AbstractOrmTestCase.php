<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Vrok\DoctrineAddons\Tests\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Vrok\DoctrineAddons\Tests\DBAL\AbstractDbalTestCase;
use Vrok\DoctrineAddons\Tests\Fixtures\TestEntity;

abstract class AbstractOrmTestCase extends AbstractDbalTestCase
{
    protected Configuration $configuration;
    protected EntityManager $em;

    protected function setUp(): void
    {
        parent::setUp();

        $configuration = ORMSetup::createAttributeMetadataConfig(
            [__DIR__.'/../Fixtures'],
            true
        );
        $configuration->enableNativeLazyObjects(true);
        $this->configuration = $configuration;
    }

    protected function buildEntityManager(
        EventManager $eventManager = new EventManager(),
    ): EntityManager {
        $conn = DriverManager::getConnection(self::getConnectionParams(), $this->configuration);
        $this->em = new EntityManager($conn, $this->configuration, $eventManager);

        return $this->em;
    }

    /**
     * @param class-string $className
     */
    protected function setupSchema(string $className = TestEntity::class): void
    {
        $tool = new SchemaTool($this->em);
        $classes = [$this->em->getClassMetadata($className)];
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }
}
