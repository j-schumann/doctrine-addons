<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Vrok\DoctrineAddons\Tests\DBAL;

use Doctrine\DBAL\Connection\StaticServerVersionProvider;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Driver\MariadbTestDriver;
use Vrok\DoctrineAddons\DBAL\Platforms\MariadbTestPlatform;
use Vrok\DoctrineAddons\Tests\TestDatabase;

final class MariadbTestDriverTest extends TestCase
{
    public function testReturnsCorrectPlatform(): void
    {
        $driver = new MariadbTestDriver();
        $platform = $driver->getDatabasePlatform(new StaticServerVersionProvider('mariadb-10.6.8'));
        self::assertInstanceOf(MariadbTestPlatform::class, $platform);
    }

    /**
     * Uses the database given in DATABASE_URL, skipped for other databases.
     */
    #[Group('database')]
    public function testTruncateIgnoresForeignKeys(): void
    {
        $params = TestDatabase::getConnectionParams();
        if (!DriverManager::getConnection($params)->getDatabasePlatform() instanceof MariaDBPlatform) {
            self::markTestSkipped('Requires a MariaDB database.');
        }

        unset($params['driver']);
        $params['driverClass'] = MariadbTestDriver::class;
        $connection = DriverManager::getConnection($params);
        $platform = $connection->getDatabasePlatform();
        self::assertInstanceOf(MariadbTestPlatform::class, $platform);

        $connection->executeStatement('DROP TABLE IF EXISTS truncate_child');
        $connection->executeStatement('DROP TABLE IF EXISTS truncate_parent');
        $connection->executeStatement('CREATE TABLE truncate_parent (id INT AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB');
        $connection->executeStatement(
            'CREATE TABLE truncate_child (id INT AUTO_INCREMENT PRIMARY KEY, parent_id INT,'
            .' FOREIGN KEY (parent_id) REFERENCES truncate_parent (id)) ENGINE=InnoDB'
        );
        $connection->executeStatement('INSERT INTO truncate_parent VALUES (1)');
        $connection->executeStatement('INSERT INTO truncate_child (parent_id) VALUES (1)');

        // a plain TRUNCATE fails for a table referenced by a foreign key
        $connection->executeStatement($platform->getTruncateTableSQL('truncate_parent'));

        self::assertSame(0, (int) $connection->fetchOne('SELECT COUNT(*) FROM truncate_parent'));
        $connection->executeStatement('DROP TABLE truncate_child');
        $connection->executeStatement('DROP TABLE truncate_parent');
    }
}
