<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Driver;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use Doctrine\DBAL\Driver\PDO\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Doctrine\DBAL\Schema\PostgreSQLSchemaManager;
use Doctrine\Deprecations\Deprecation;
use PDO;
use PDOException;
use Vrok\DoctrineAddons\DBAL\Platforms\MariadbTestPlatform;
use Vrok\DoctrineAddons\DBAL\Platforms\PostgreSQLTestPlatform;

/**
 * We simply want to customize the generated TRUNCATE sql because
 * DoctrineFixtures\ORMPurger does not delete leaf tables first and causes
 * "1701 Cannot truncate a table referenced in a foreign key constraint".
 * But because we can only override driver_class in the config, and not the
 * platform, we have to implement this driver and override
 * createDatabasePlatformForVersion().
 * And because someone defined PDO\Mysql\Driver final we have to copy all methods from there
 * and inherit from AbstractMySQLDriver.
 * And because someone defined the version-check functions as private we have to implement
 * them here too...
 */
class PostgreSQLTestDriver extends AbstractPostgreSQLDriver
{
    /**
     * {@inheritdoc}
     */
    public function createDatabasePlatformForVersion($version)
    {
        return new PostgreSQLTestPlatform();
    }

    /**
     * {@inheritdoc}
     *
     * @return Connection
     */
    public function connect(array $params)
    {
        $driverOptions = $params['driverOptions'] ?? [];

        if (! empty($params['persistent'])) {
            $driverOptions[PDO::ATTR_PERSISTENT] = true;
        }

        try {
            $pdo = new PDO(
                $this->constructPdoDsn($params),
                $params['user'] ?? '',
                $params['password'] ?? '',
                $driverOptions
            );
        } catch (PDOException $exception) {
            throw \Doctrine\DBAL\Driver\PDO\Exception::new($exception);
        }

        if (
            ! isset($driverOptions[PDO::PGSQL_ATTR_DISABLE_PREPARES])
            || $driverOptions[PDO::PGSQL_ATTR_DISABLE_PREPARES] === true
        ) {
            $pdo->setAttribute(PDO::PGSQL_ATTR_DISABLE_PREPARES, true);
        }

        $connection = new Connection($pdo);

        /* defining client_encoding via SET NAMES to avoid inconsistent DSN support
         * - passing client_encoding via the 'options' param breaks pgbouncer support
         */
        if (isset($params['charset'])) {
            $connection->exec('SET NAMES \'' . $params['charset'] . '\'');
        }

        return $connection;
    }

    /**
     * Constructs the Postgres PDO DSN.
     *
     * @param mixed[] $params
     */
    private function constructPdoDsn(array $params): string
    {
        $dsn = 'pgsql:';

        if (isset($params['host']) && $params['host'] !== '') {
            $dsn .= 'host=' . $params['host'] . ';';
        }

        if (isset($params['port']) && $params['port'] !== '') {
            $dsn .= 'port=' . $params['port'] . ';';
        }

        if (isset($params['dbname'])) {
            $dsn .= 'dbname=' . $params['dbname'] . ';';
        } elseif (isset($params['default_dbname'])) {
            $dsn .= 'dbname=' . $params['default_dbname'] . ';';
        } else {
            // Used for temporary connections to allow operations like dropping the database currently connected to.
            // Connecting without an explicit database does not work, therefore "postgres" database is used
            // as it is mostly present in every server setup.
            $dsn .= 'dbname=postgres;';
        }

        if (isset($params['sslmode'])) {
            $dsn .= 'sslmode=' . $params['sslmode'] . ';';
        }

        if (isset($params['sslrootcert'])) {
            $dsn .= 'sslrootcert=' . $params['sslrootcert'] . ';';
        }

        if (isset($params['sslcert'])) {
            $dsn .= 'sslcert=' . $params['sslcert'] . ';';
        }

        if (isset($params['sslkey'])) {
            $dsn .= 'sslkey=' . $params['sslkey'] . ';';
        }

        if (isset($params['sslcrl'])) {
            $dsn .= 'sslcrl=' . $params['sslcrl'] . ';';
        }

        if (isset($params['application_name'])) {
            $dsn .= 'application_name=' . $params['application_name'] . ';';
        }

        return $dsn;
    }
}
