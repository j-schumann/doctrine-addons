<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Vrok\DoctrineAddons\Tests\DBAL;

use Doctrine\DBAL\Connection\StaticServerVersionProvider;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use Vrok\DoctrineAddons\DBAL\Driver\PostgreSQLTestDriver;
use Vrok\DoctrineAddons\DBAL\Platforms\PostgreSQLTestPlatform;

// the class is deprecated, its tests run until it is removed in 4.0
#[IgnoreDeprecations]
final class PostgreSQLTestDriverTest extends AbstractDbalTestCase
{
    public function testIsDeprecated(): void
    {
        $this->expectUserDeprecationMessageMatches(
            '/^'.preg_quote('The "'.PostgreSQLTestDriver::class.'" is deprecated', '/').'/'
        );

        new PostgreSQLTestDriver();
    }

    public function testReturnsCorrectPlatform(): void
    {
        $driver = new PostgreSQLTestDriver();
        $platform = $driver->getDatabasePlatform(new StaticServerVersionProvider('14'));
        self::assertInstanceOf(PostgreSQLTestPlatform::class, $platform);
    }

    public function testConnectInterpretsParams(): void
    {
        $this->expectException(Exception::class);

        // the IP depends on the resolution of localhost, e.g. ::1 when a server
        // is running
        $this->expectExceptionMessageMatches(
            '/connection to server at "localhost" \\(.+\\), port 5432 failed/'
        );

        $driver = new PostgreSQLTestDriver();
        $driver->connect([
            'dbname'        => 'db',
            'driverOptions' => ['driver' => 'pdo_sqlite', 'memory' => true],
            'host'          => 'localhost',
            'port'          => '5432',
            'user'          => 'user',
        ]);
    }

    public function testTruncateRestartsIdentity(): void
    {
        $params = self::getConnectionParams();
        $connection = DriverManager::getConnection($params);
        if (!$connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            self::markTestSkipped('Requires a Postgres database.');
        }

        unset($params['driver']);
        $params['driverClass'] = PostgreSQLTestDriver::class;
        $connection = DriverManager::getConnection($params);
        $platform = $connection->getDatabasePlatform();
        self::assertInstanceOf(PostgreSQLTestPlatform::class, $platform);

        $connection->executeStatement('DROP TABLE IF EXISTS truncate_test');
        $connection->executeStatement(
            'CREATE TABLE truncate_test (id SERIAL PRIMARY KEY, name VARCHAR(10))'
        );
        $connection->executeStatement("INSERT INTO truncate_test (name) VALUES ('a'), ('b')");

        $connection->executeStatement($platform->getTruncateTableSQL('truncate_test'));
        $connection->executeStatement("INSERT INTO truncate_test (name) VALUES ('c')");

        self::assertSame(1, (int) $connection->fetchOne('SELECT id FROM truncate_test'));
        $connection->executeStatement('DROP TABLE truncate_test');
    }
}
