<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\DBAL;

use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Platforms\PostgreSQLTestPlatform;

class PostgreSQLTestPlatformTest extends TestCase
{
    public function testGetTruncateTableSQL()
    {
        $platform = new PostgreSQLTestPlatform();
        $sql = $platform->getTruncateTableSQL('the-table', true);
        $this->assertSame(
            'TRUNCATE the-table RESTART IDENTITY CASCADE',
            $sql
        );
    }
}
