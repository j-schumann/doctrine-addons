<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Vrok\DoctrineAddons\Tests\DBAL;

use Doctrine\DBAL\Connection\StaticServerVersionProvider;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use Vrok\DoctrineAddons\DBAL\Driver\MariadbTestDriver;
use Vrok\DoctrineAddons\DBAL\Platforms\MariadbTestPlatform;

// the class is deprecated, its tests run until it is removed in 4.0
#[IgnoreDeprecations]
final class MariadbTestDriverTest extends AbstractDbalTestCase
{
    public function testIsDeprecated(): void
    {
        $this->expectUserDeprecationMessageMatches(
            '/^'.preg_quote('The "'.MariadbTestDriver::class.'" is deprecated', '/').'/'
        );

        new MariadbTestDriver();
    }

    public function testReturnsCorrectPlatform(): void
    {
        $driver = new MariadbTestDriver();
        $platform = $driver->getDatabasePlatform(new StaticServerVersionProvider('mariadb-10.6.8'));
        self::assertInstanceOf(MariadbTestPlatform::class, $platform);
    }

    public function testTruncateIgnoresForeignKeys(): void
    {
        $params = self::getConnectionParams();
        $connection = DriverManager::getConnection($params);
        if (!$connection->getDatabasePlatform() instanceof MariaDBPlatform) {
            self::markTestSkipped('Requires a MariaDB database.');
        }

        unset($params['driver']);
        $params['driverClass'] = MariadbTestDriver::class;
        $connection = DriverManager::getConnection($params);
        $platform = $connection->getDatabasePlatform();
        self::assertInstanceOf(MariadbTestPlatform::class, $platform);

        $connection->executeStatement('DROP TABLE IF EXISTS truncate_child');
        $connection->executeStatement('DROP TABLE IF EXISTS truncate_parent');
        $connection->executeStatement(
            'CREATE TABLE truncate_parent (id INT AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB'
        );
        $connection->executeStatement(
            'CREATE TABLE truncate_child (id INT AUTO_INCREMENT PRIMARY KEY, parent_id INT,'
            .' FOREIGN KEY (parent_id) REFERENCES truncate_parent (id)) ENGINE=InnoDB'
        );
        $connection->executeStatement('INSERT INTO truncate_parent VALUES (1)');
        $connection->executeStatement('INSERT INTO truncate_child (parent_id) VALUES (1)');

        // a plain TRUNCATE fails for a table referenced by a foreign key
        $connection->executeStatement($platform->getTruncateTableSQL('truncate_parent'));

        self::assertSame(
            0,
            (int) $connection->fetchOne('SELECT COUNT(*) FROM truncate_parent')
        );
        $connection->executeStatement('DROP TABLE truncate_child');
        $connection->executeStatement('DROP TABLE truncate_parent');
    }
}
