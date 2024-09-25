<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\DBAL;

use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Platforms\MariadbTestPlatform;

class MariadbTestPlatformTest extends TestCase
{
    public function testGetTruncateTableSQL(): void
    {
        $platform = new MariadbTestPlatform();
        $sql = $platform->getTruncateTableSQL('the-table');
        self::assertSame('SET foreign_key_checks = 0;TRUNCATE the-table;SET foreign_key_checks = 1;', $sql);
    }
}
