<?php

namespace Vrok\DoctrineAddons\Tests\DBAL;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Platforms\MariadbTestPlatform;

// the class is deprecated, its tests run until it is removed in 4.0
#[IgnoreDeprecations]
final class MariadbTestPlatformTest extends TestCase
{
    public function testIsDeprecated(): void
    {
        $this->expectUserDeprecationMessageMatches(
            '/^'.preg_quote('The "'.MariadbTestPlatform::class.'" is deprecated', '/').'/'
        );

        new MariadbTestPlatform();
    }

    public function testGetTruncateTableSql(): void
    {
        $platform = new MariadbTestPlatform();
        $sql = $platform->getTruncateTableSQL('the-table');
        self::assertSame('SET foreign_key_checks = 0;TRUNCATE the-table;SET foreign_key_checks = 1;', $sql);
    }
}
