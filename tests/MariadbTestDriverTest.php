<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests;

use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Driver\MariadbTestDriver;
use Vrok\DoctrineAddons\DBAL\Platforms\MariadbTestPlatform;

class MariadbTestDriverTest extends TestCase
{
    public function testReturnsCorrectPlatform()
    {
        $driver = new MariadbTestDriver();
        $platform = $driver->createDatabasePlatformForVersion('mariadb-10.4.8');
        $this->assertInstanceOf(MariadbTestPlatform::class, $platform);
    }
}
