<?php

namespace Vrok\DoctrineAddons\Tests\DBAL;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Platforms\PostgreSQLTestPlatform;

// the class is deprecated, its tests run until it is removed in 4.0
#[IgnoreDeprecations]
final class PostgreSQLTestPlatformTest extends TestCase
{
    public function testIsDeprecated(): void
    {
        $this->expectUserDeprecationMessageMatches(
            '/^'.preg_quote('The "'.PostgreSQLTestPlatform::class.'" is deprecated', '/').'/'
        );

        new PostgreSQLTestPlatform();
    }

    public function testGetTruncateTableSql(): void
    {
        $platform = new PostgreSQLTestPlatform();
        $sql = $platform->getTruncateTableSQL('the-table', true);
        self::assertSame('TRUNCATE "the-table" RESTART IDENTITY CASCADE', $sql);
    }
}
