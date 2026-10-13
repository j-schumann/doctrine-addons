<?php

namespace Vrok\DoctrineAddons\DBAL\Platforms;

use Doctrine\DBAL\Platforms\MariaDB1052Platform;

/**
 * We just want to override the getTruncateTableSQL() to ignore foreign keys,
 * see MariadbTestDriver.
 *
 * @deprecated since 3.2, removed in 4.0, use the default driver, the RefreshDatabaseTrait of
 *             vrok/symfony-addons handles the foreign keys and resets the identities
 */
class MariadbTestPlatform extends MariaDB1052Platform
{
    public function __construct()
    {
        @trigger_error(\sprintf('The "%s" is deprecated and removed in 4.0, use the default driver, the RefreshDatabaseTrait of vrok/symfony-addons handles the foreign keys and resets the identities.', self::class), \E_USER_DEPRECATED);

        parent::__construct();
    }

    #[\Override]
    public function getTruncateTableSQL($tableName, $cascade = false): string
    {
        return \sprintf('SET foreign_key_checks = 0;TRUNCATE %s;SET foreign_key_checks = 1;', $tableName);
    }
}
