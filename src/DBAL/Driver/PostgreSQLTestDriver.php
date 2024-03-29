<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Driver;

use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use Doctrine\DBAL\Driver\PDO\Connection;
use PDO;
use Vrok\DoctrineAddons\DBAL\Platforms\PostgreSQLTestPlatform;

/**
 * We simply want to customize the generated TRUNCATE sql because
 * otherwise sequences/identity autoincrements aren't reset.
 * But because we can only override driver_class in the config, and not the
 * platform, we have to implement this driver and override
 * createDatabasePlatformForVersion().
 * And because someone defined Doctrine\DBAL\Driver\PDO\PgSQL\Driver final we have
 * to copy all methods from there and inherit from AbstractPostgreSQLDriver.
 */
class PostgreSQLTestDriver extends AbstractPostgreSQLDriver
{
    public function createDatabasePlatformForVersion($version): PostgreSQLTestPlatform
    {
        return new PostgreSQLTestPlatform();
    }

    public function connect(array $params): Connection
    {
        $driverOptions = $params['driverOptions'] ?? [];

        if (!empty($params['persistent'])) {
            $driverOptions[\PDO::ATTR_PERSISTENT] = true;
        }

        try {
            $pdo = new \PDO(
                $this->constructPdoDsn($params),
                $params['user'] ?? '',
                $params['password'] ?? '',
                $driverOptions
            );
        } catch (\PDOException $exception) {
            throw \Doctrine\DBAL\Driver\PDO\Exception::new($exception);
        }

        if (
            !isset($driverOptions[\PDO::PGSQL_ATTR_DISABLE_PREPARES])
            || true === $driverOptions[\PDO::PGSQL_ATTR_DISABLE_PREPARES]
        ) {
            $pdo->setAttribute(\PDO::PGSQL_ATTR_DISABLE_PREPARES, true);
        }

        $connection = new Connection($pdo);

        /* defining client_encoding via SET NAMES to avoid inconsistent DSN support
         * - passing client_encoding via the 'options' param breaks pgbouncer support
         */
        if (isset($params['charset'])) {
            $connection->exec('SET NAMES \''.$params['charset'].'\'');
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

        if (isset($params['host']) && '' !== $params['host']) {
            $dsn .= 'host='.$params['host'].';';
        }

        if (isset($params['port']) && '' !== $params['port']) {
            $dsn .= 'port='.$params['port'].';';
        }

        if (isset($params['dbname'])) {
            $dsn .= 'dbname='.$params['dbname'].';';
        } elseif (isset($params['default_dbname'])) {
            $dsn .= 'dbname='.$params['default_dbname'].';';
        } else {
            // Used for temporary connections to allow operations like dropping the database currently connected to.
            // Connecting without an explicit database does not work, therefore "postgres" database is used
            // as it is mostly present in every server setup.
            $dsn .= 'dbname=postgres;';
        }

        if (isset($params['sslmode'])) {
            $dsn .= 'sslmode='.$params['sslmode'].';';
        }

        if (isset($params['sslrootcert'])) {
            $dsn .= 'sslrootcert='.$params['sslrootcert'].';';
        }

        if (isset($params['sslcert'])) {
            $dsn .= 'sslcert='.$params['sslcert'].';';
        }

        if (isset($params['sslkey'])) {
            $dsn .= 'sslkey='.$params['sslkey'].';';
        }

        if (isset($params['sslcrl'])) {
            $dsn .= 'sslcrl='.$params['sslcrl'].';';
        }

        if (isset($params['application_name'])) {
            $dsn .= 'application_name='.$params['application_name'].';';
        }

        return $dsn;
    }
}
