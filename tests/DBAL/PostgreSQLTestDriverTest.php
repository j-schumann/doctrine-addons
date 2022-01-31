<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\DBAL;

use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Driver\PostgreSQLTestDriver;
use Vrok\DoctrineAddons\DBAL\Platforms\PostgreSQLTestPlatform;

class PostgreSQLTestDriverTest extends TestCase
{
    public function testReturnsCorrectPlatform()
    {
        $driver = new PostgreSQLTestDriver();
        $platform = $driver->createDatabasePlatformForVersion('14');
        $this->assertInstanceOf(PostgreSQLTestPlatform::class, $platform);
    }
}
