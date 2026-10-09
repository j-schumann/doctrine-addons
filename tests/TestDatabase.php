<?php

namespace Vrok\DoctrineAddons\Tests;

use Doctrine\DBAL\Tools\DsnParser;

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
}
