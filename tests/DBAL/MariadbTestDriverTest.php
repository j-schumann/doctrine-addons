<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\DBAL;

use Doctrine\DBAL\Connection\StaticServerVersionProvider;
use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Driver\MariadbTestDriver;
use Vrok\DoctrineAddons\DBAL\Platforms\MariadbTestPlatform;

final class MariadbTestDriverTest extends TestCase
{
    public function testReturnsCorrectPlatform(): void
    {
        $driver = new MariadbTestDriver();
        $platform = $driver->getDatabasePlatform(new StaticServerVersionProvider('mariadb-10.6.8'));
        self::assertInstanceOf(MariadbTestPlatform::class, $platform);
    }
}
