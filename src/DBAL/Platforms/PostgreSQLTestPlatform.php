<?php

namespace Vrok\DoctrineAddons\DBAL\Platforms;

use Doctrine\DBAL\Platforms\PostgreSQL120Platform;
use Doctrine\DBAL\Schema\Identifier;

/**
 * @deprecated since 3.2, removed in 4.0, use the default driver, the RefreshDatabaseTrait of
 *             vrok/symfony-addons resets the identities
 */
class PostgreSQLTestPlatform extends PostgreSQL120Platform
{
    public function __construct()
    {
        @trigger_error(\sprintf('The "%s" is deprecated and removed in 4.0, use the default driver, the RefreshDatabaseTrait of vrok/symfony-addons resets the identities.', self::class), \E_USER_DEPRECATED);

        parent::__construct();
    }

    #[\Override]
    public function getTruncateTableSQL($tableName, $cascade = false): string
    {
        $tableIdentifier = new Identifier($tableName);
        $sql = 'TRUNCATE '.$tableIdentifier->getObjectName()->toSQL($this).' RESTART IDENTITY';

        if ($cascade) {
            $sql .= ' CASCADE';
        }

        return $sql;
    }
}
