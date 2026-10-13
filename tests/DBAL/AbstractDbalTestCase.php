<?php

namespace Vrok\DoctrineAddons\Tests\DBAL;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

/**
 * Connects to the database given in DATABASE_URL, phpunit.xml.dist defaults it to an in-memory
 * SQLite database, the CI jobs set the other platforms.
 */
abstract class AbstractDbalTestCase extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    protected static function getConnectionParams(): array
    {
        $url = getenv('DATABASE_URL');
        if (!\is_string($url) || '' === $url) {
            throw new \LogicException('DATABASE_URL is not set, see phpunit.xml.dist.');
        }

        return new DsnParser([
            'mysql'  => 'pdo_mysql',
            'pgsql'  => 'pdo_pgsql',
            'sqlite' => 'pdo_sqlite',
            'sqlsrv' => 'sqlsrv',
        ])->parse($url);
    }

    /**
     * @return array{0: mixed, 1: mixed} the raw database value and the value converted by the type
     */
    protected static function storeAndLoad(Type $type, mixed $value): array
    {
        $connection = DriverManager::getConnection(self::getConnectionParams());
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
