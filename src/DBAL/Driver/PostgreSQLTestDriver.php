<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Driver;

use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use Doctrine\DBAL\Driver\PDO\Connection;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Driver\PDO\Exception\InvalidConfiguration;
use Doctrine\DBAL\Driver\PDO\PDOConnect;
use Doctrine\DBAL\Platforms\Exception\InvalidPlatformVersion;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\ServerVersionProvider;
use Doctrine\Deprecations\Deprecation;
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
    use PDOConnect;

    public function getDatabasePlatform(ServerVersionProvider $versionProvider): PostgreSQLPlatform
    {
        $version = $versionProvider->getServerVersion();

        if (1 !== preg_match('/^(?P<major>\d+)(?:\.(?P<minor>\d+)(?:\.(?P<patch>\d+))?)?/', $version, $versionParts)) {
            throw InvalidPlatformVersion::new($version, '<major_version>.<minor_version>.<patch_version>');
        }

        $majorVersion = $versionParts['major'];
        $minorVersion = $versionParts['minor'] ?? 0;
        $patchVersion = $versionParts['patch'] ?? 0;
        $version      = $majorVersion.'.'.$minorVersion.'.'.$patchVersion;

        if (version_compare($version, '12.0', '>=')) {
            return new PostgreSQLTestPlatform();
        }

        Deprecation::trigger(
            'doctrine/dbal',
            'https://github.com/doctrine/dbal/pull/6495',
            'Support for Postgres < 12 is deprecated and will be removed in DBAL 5',
        );

        return new PostgreSQLPlatform();
    }

    public function connect(
        #[\SensitiveParameter]
        array $params,
    ): Connection {
        $driverOptions = $params['driverOptions'] ?? [];

        if (!empty($params['persistent'])) {
            $driverOptions[\PDO::ATTR_PERSISTENT] = true;
        }

        foreach (['user', 'password'] as $key) {
            if (isset($params[$key]) && !is_string($params[$key])) {
                throw InvalidConfiguration::notAStringOrNull($key, $params[$key]);
            }
        }

        $safeParams = $params;
        unset($safeParams['password']);

        try {
            $pdo = $this->doConnect(
                $this->constructPdoDsn($safeParams),
                $params['user'] ?? '',
                $params['password'] ?? '',
                $driverOptions,
            );
        } catch (\PDOException $exception) {
            throw Exception::new($exception);
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
     * @param array<string, mixed> $params
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

        if (isset($params['gssencmode'])) {
            $dsn .= 'gssencmode='.$params['gssencmode'].';';
        }

        return $dsn;
    }
}
