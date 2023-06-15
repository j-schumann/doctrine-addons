<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Platforms;

use Doctrine\DBAL\Platforms\MariaDBPlatform;

/**
 * We just want to override the getTruncateTableSQL() to ignore foreign keys,
 * see MariadbTestDriver.
 */
class MariadbTestPlatform extends MariaDBPlatform
{
    public function getTruncateTableSQL($tableName, $cascade = false): string
    {
        return sprintf('SET foreign_key_checks = 0;TRUNCATE %s;SET foreign_key_checks = 1;', $tableName);
    }
}
