<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Tests;

use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\DBAL\Platforms\MariadbTestPlatform;

class MariadbTestPlatformTest extends TestCase
{
    public function testGetTruncateTableSQL()
    {
        $platform = new MariadbTestPlatform();
        $sql = $platform->getTruncateTableSQL('the-table');
        $this->assertSame(
            'SET foreign_key_checks = 0;TRUNCATE the-table;SET foreign_key_checks = 1;',
            $sql
        );
    }
}
