<?php

namespace Vrok\DoctrineAddons\DBAL\Platforms;

use Doctrine\DBAL\Platforms\MariaDB1052Platform;

/**
 * We just want to override the getTruncateTableSQL() to ignore foreign keys,
 * see MariadbTestDriver.
 */
// @phpstan-ignore class.extendsDeprecatedClass (the oldest platform the tests need)
class MariadbTestPlatform extends MariaDB1052Platform
{
    #[\Override]
    public function getTruncateTableSQL(string $tableName, bool $cascade = false): string
    {
        return \sprintf('SET foreign_key_checks = 0;TRUNCATE %s;SET foreign_key_checks = 1;', $tableName);
    }
}
