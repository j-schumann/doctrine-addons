<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Platforms;

use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use Doctrine\DBAL\Schema\Identifier;

class PostgreSQLTestPlatform extends PostgreSQL100Platform
{
    /**
     * {@inheritDoc}
     */
    public function getTruncateTableSQL($tableName, $cascade = false)
    {
        $tableIdentifier = new Identifier($tableName);
        $sql = 'TRUNCATE ' . $tableIdentifier->getQuotedName($this) . ' RESTART IDENTITY';

        if ($cascade) {
            $sql .= ' CASCADE';
        }

        return $sql;
    }
}
