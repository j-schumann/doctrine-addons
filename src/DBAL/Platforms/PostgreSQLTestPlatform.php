<?php

namespace Vrok\DoctrineAddons\DBAL\Platforms;

use Doctrine\DBAL\Platforms\PostgreSQL120Platform;
use Doctrine\DBAL\Schema\Identifier;

// @phpstan-ignore class.extendsDeprecatedClass (the oldest platform the tests need)
class PostgreSQLTestPlatform extends PostgreSQL120Platform
{
    #[\Override]
    public function getTruncateTableSQL(string $tableName, bool $cascade = false): string
    {
        $tableIdentifier = new Identifier($tableName);
        $sql = 'TRUNCATE '.$tableIdentifier->getObjectName()->toSQL($this).' RESTART IDENTITY';

        if ($cascade) {
            $sql .= ' CASCADE';
        }

        return $sql;
    }
}
