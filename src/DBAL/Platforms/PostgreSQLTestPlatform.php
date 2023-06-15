<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Platforms;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Identifier;

class PostgreSQLTestPlatform extends PostgreSQLPlatform
{
    public function getTruncateTableSQL($tableName, $cascade = false): string
    {
        $tableIdentifier = new Identifier($tableName);
        $sql = 'TRUNCATE '.$tableIdentifier->getQuotedName($this).' RESTART IDENTITY';

        if ($cascade) {
            $sql .= ' CASCADE';
        }

        return $sql;
    }
}
