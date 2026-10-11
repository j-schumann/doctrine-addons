<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Vrok\DoctrineAddons\Tests\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Gedmo\Sluggable\SluggableListener;
use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\Tests\Fixtures\SlugEntity;
use Vrok\DoctrineAddons\Tests\Fixtures\TestEntity;
use Vrok\DoctrineAddons\Tests\TestDatabase;
use Vrok\DoctrineAddons\Util\UmlautTransliterator;

abstract class AbstractOrmTestCase extends TestCase
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
     * Uses an in-memory SQLite database by default, so tests of the generated
     * SQL do not depend on the database the tests run against.
     *
     * @param array<string, mixed> $connectionParams
     */
    protected function buildEntityManager(array $connectionParams = ['driver' => 'pdo_sqlite', 'memory' => true]): EntityManager
    {
        $conn = DriverManager::getConnection($connectionParams, $this->configuration);

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

    /**
     * Uses the database given in DATABASE_URL, for tests that run real queries.
     */
    protected function buildDatabaseEntityManager(): EntityManager
    {
        return $this->buildEntityManager(TestDatabase::getConnectionParams());
    }

    /**
     * @param class-string<AbstractPlatform> $platformClass
     */
    protected function skipUnlessPlatform(string $platformClass): void
    {
        $platform = $this->em->getConnection()->getDatabasePlatform();
        if (!$platform instanceof $platformClass) {
            self::markTestSkipped(\sprintf('Requires %s, the tests run with %s.', $platformClass, $platform::class));
        }
    }

    /**
     * @param list<class-string> $classes
     */
    protected function createTables(array $classes): void
    {
        $tool = new SchemaTool($this->em);
        $metadata = array_map($this->em->getClassMetadata(...), $classes);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
    }

    /**
     * Uses the database given in DATABASE_URL, skips the test unless it is
     * Postgres. Register the DQL functions to test before.
     */
    protected function setupPostgresRecords(): void
    {
        $this->buildDatabaseEntityManager();
        $this->skipUnlessPlatform(PostgreSQLPlatform::class);
        $this->createTables([TestEntity::class]);

        foreach ([
            'roles'   => ['ROLE_ADMIN', 'ROLE_EDITOR'],
            'blog'    => ['ROLE_ADMIN_BLOG'],
            'numbers' => [1, 5],
            'address' => ['city' => 'Dresden', 'zip' => '01069'],
        ] as $name => $json) {
            $record = new TestEntity();
            $record->name = $name;
            $record->jsonColumn = $json;
            $this->em->persist($record);
        }

        $this->em->flush();
    }

    /**
     * @return list<string> the names of the TestEntity records matching the condition
     */
    protected function findNames(string $condition, mixed $value): array
    {
        $result = $this->em->createQuery(
            'SELECT t.name FROM '.TestEntity::class." t WHERE $condition ORDER BY t.name"
        )
            ->setParameter('value', $value)
            ->getSingleColumnResult();

        return array_values($result);
    }

    protected function setupSchema(): void
    {
        if (!$this->em) {
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
