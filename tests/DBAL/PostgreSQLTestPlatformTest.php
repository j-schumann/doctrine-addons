<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\DBAL;

use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Platforms\PostgreSQLTestPlatform;

final class PostgreSQLTestPlatformTest extends TestCase
{
    public function testGetTruncateTableSql(): void
    {
        $platform = new PostgreSQLTestPlatform();
        $sql = $platform->getTruncateTableSQL('the-table', true);
        self::assertSame('TRUNCATE "the-table" RESTART IDENTITY CASCADE', $sql);
    }
}
