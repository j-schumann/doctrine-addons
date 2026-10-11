<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Vrok\DoctrineAddons\Tests\Fixtures\TestEntity;
use Vrok\DoctrineAddons\Tests\ORM\AbstractOrmTestCase;

/**
 * For the Postgres-only DQL functions: besides the generated SQL, they are
 * tested with real queries when the tests run against Postgres.
 */
abstract class AbstractPostgresFunctionTestCase extends AbstractOrmTestCase
{
    /**
     * Skips the test unless DATABASE_URL points to Postgres. Register the DQL
     * function to test before.
     */
    protected function setupRecords(): void
    {
        $this->buildEntityManager();
        if (!$this->em->getConnection()->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            self::markTestSkipped('Requires a Postgres database.');
        }

        $this->setupSchema();

        foreach ([
            'address' => ['city' => 'Dresden', 'zip' => '01069'],
            'blog'    => ['ROLE_ADMIN_BLOG'],
            'numbers' => [1, 5],
            'roles'   => ['ROLE_ADMIN', 'ROLE_EDITOR'],
        ] as $name => $json) {
            $record = new TestEntity();
            $record->name = $name;
            $record->jsonColumn = $json;
            $this->em->persist($record);
        }

        $this->em->flush();
    }

    /**
     * @return list<string> the names of the records matching the condition
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
}
