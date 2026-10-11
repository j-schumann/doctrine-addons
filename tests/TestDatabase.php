<?php

namespace Vrok\DoctrineAddons\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Types\Type;

/**
 * Provides the connection parameters for the database the integration tests
 * run against, see the CI jobs.
 */
final class TestDatabase
{
    /**
     * Returns the connection parameters for the database given in the
     * DATABASE_URL environment variable, falls back to an in-memory SQLite
     * database.
     *
     * @return array<string, mixed>
     */
    public static function getConnectionParams(): array
    {
        $url = $_SERVER['DATABASE_URL'] ?? $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');
        if (!\is_string($url) || '' === $url) {
            return ['driver' => 'pdo_sqlite', 'memory' => true];
        }

        return new DsnParser([
            'mysql'  => 'pdo_mysql',
            'pgsql'  => 'pdo_pgsql',
            'sqlite' => 'pdo_sqlite',
            'sqlsrv' => 'sqlsrv',
        ])->parse($url);
    }

    public static function getConnection(): Connection
    {
        return DriverManager::getConnection(self::getConnectionParams());
    }

    /**
     * Stores the value in a column of the given type and loads it again.
     *
     * @return array{0: mixed, 1: mixed} the raw database value and the value converted by the type
     */
    public static function storeAndLoad(Type $type, mixed $value): array
    {
        $connection = self::getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement('DROP TABLE IF EXISTS type_test');
        $connection->executeStatement(\sprintf(
            'CREATE TABLE type_test (test_value %s NULL)',
            $type->getSQLDeclaration([], $platform)
        ));
        $connection->insert('type_test', ['test_value' => $value], ['test_value' => $type]);

        $raw = $connection->fetchOne('SELECT test_value FROM type_test');
        $connection->executeStatement('DROP TABLE type_test');

        return [$raw, $type->convertToPHPValue($raw, $platform)];
    }
}
