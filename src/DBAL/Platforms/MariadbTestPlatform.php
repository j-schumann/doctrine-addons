<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Platforms;

// @todo use this base class instead of switching when support for DBAL@2.x.x is removed
//use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\Types;
use Doctrine\Deprecations\Deprecation;

/**
 * We just want to override the getTruncateTableSQL() but because someone
 * defined MariaDb1027Platform as final class we have to duplicate all other
 * methods from there...
 */
class MariadbTestPlatform extends MySQLPlatform
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
     * @see https://mariadb.com/kb/en/library/json-data-type/
     */
    public function getJsonTypeDeclarationSQL(array $column): string
    {
        return 'LONGTEXT';
    }

    /**
     * @deprecated implement {@link createReservedKeywordsList()} instead
     */
    protected function getReservedKeywordsClass(): string
    {
        Deprecation::triggerIfCalledFromOutside(
            'doctrine/dbal',
            'https://github.com/doctrine/dbal/issues/4510',
            'MariaDb1027Platform::getReservedKeywordsClass() is deprecated,'
            .' use MariaDb1027Platform::createReservedKeywordsList() instead.'
        );

        return Keywords\MariaDb102Keywords::class;
    }

    protected function initializeDoctrineTypeMappings(): void
    {
        parent::initializeDoctrineTypeMappings();

        $this->doctrineTypeMapping['json'] = Types::JSON;
    }
}
