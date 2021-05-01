<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Platforms;

use Doctrine\DBAL\Platforms\Keywords\MariaDb102Keywords;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Types;
use Doctrine\Deprecations\Deprecation;

/**
 * We just want to override the getTruncateTableSQL() but because someone
 * defined MariaDb1027Platform as final class we have to duplicate all other
 * methods from there...
 */
class MariadbTestPlatform extends MySqlPlatform
{
    /**
     * {@inheritdoc}
     */
    public function getTruncateTableSQL($tableName, $cascade = false)
    {
        return sprintf('SET foreign_key_checks = 0;TRUNCATE %s;SET foreign_key_checks = 1;', $tableName);
    }

    /**
     * {@inheritdoc}
     *
     * @link https://mariadb.com/kb/en/library/json-data-type/
     */
    public function getJsonTypeDeclarationSQL(array $column): string
    {
        return 'LONGTEXT';
    }

    /**
     * @deprecated Implement {@link createReservedKeywordsList()} instead.
     */
    protected function getReservedKeywordsClass(): string
    {
        Deprecation::triggerIfCalledFromOutside(
            'doctrine/dbal',
            'https://github.com/doctrine/dbal/issues/4510',
            'MariaDb1027Platform::getReservedKeywordsClass() is deprecated,'
            . ' use MariaDb1027Platform::createReservedKeywordsList() instead.'
        );

        return Keywords\MariaDb102Keywords::class;
    }

    protected function initializeDoctrineTypeMappings(): void
    {
        parent::initializeDoctrineTypeMappings();

        $this->doctrineTypeMapping['json'] = Types::JSON;
    }
}
