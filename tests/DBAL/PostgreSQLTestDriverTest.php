<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\DBAL;

use Doctrine\DBAL\Connection\StaticServerVersionProvider;
use Doctrine\DBAL\Driver\PDO\Exception;
use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Driver\PostgreSQLTestDriver;
use Vrok\DoctrineAddons\DBAL\Platforms\PostgreSQLTestPlatform;

final class PostgreSQLTestDriverTest extends TestCase
{
    public function testReturnsCorrectPlatform(): void
    {
        $driver = new PostgreSQLTestDriver();
        $platform = $driver->getDatabasePlatform(new StaticServerVersionProvider('14'));
        self::assertInstanceOf(PostgreSQLTestPlatform::class, $platform);
    }

    public function testConnectInterpretsParams(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('connection to server at "localhost" (127.0.0.1), port 5432 failed');

        $driver = new PostgreSQLTestDriver();
        $driver->connect([
            'dbname'        => 'db',
            'driverOptions' => ['driver' => 'pdo_sqlite', 'memory' => true],
            'host'          => 'localhost',
            'port'          => '5432',
            'user'          => 'user',
        ]);
    }
}
