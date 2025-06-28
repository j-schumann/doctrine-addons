<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Driver;

use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\PDO\Connection;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\Exception\InvalidPlatformVersion;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Platforms\MySQL84Platform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\ServerVersionProvider;
use Doctrine\Deprecations\Deprecation;
use Vrok\DoctrineAddons\DBAL\Platforms\MariadbTestPlatform;

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
class MariadbTestDriver extends AbstractMySQLDriver
{
    #[\Override]
    public function getDatabasePlatform(ServerVersionProvider $versionProvider): AbstractMySQLPlatform
    {
        $version = $versionProvider->getServerVersion();
        if (false !== stripos($version, 'mariadb')) {
            $mariaDbVersion = $this->getMariaDbMysqlVersionNumber($version);
            if (version_compare($mariaDbVersion, '10.5.2', '>=')) {
                return new MariadbTestPlatform();
            }

            Deprecation::trigger(
                'doctrine/dbal',
                'https://github.com/doctrine/dbal/pull/6343',
                'Support for MariaDB < 10.5.2 is deprecated and will be removed in DBAL 5',
            );

            return new MariaDBPlatform();
        }

        if (version_compare($version, '8.4.0', '>=')) {
            return new MySQL84Platform();
        }

        if (version_compare($version, '8.0.0', '>=')) {
            return new MySQL80Platform();
        }

        Deprecation::trigger(
            'doctrine/dbal',
            'https://github.com/doctrine/dbal/pull/6343',
            'Support for MySQL < 8 is deprecated and will be removed in DBAL 5',
        );

        return new MySQLPlatform();
    }

    /**
     * Detect MariaDB server version, including hack for some mariadb distributions
     * that starts with the prefix '5.5.5-'.
     *
     * @param string $versionString Version string as returned by mariadb server, i.e. '5.5.5-Mariadb-10.0.8-xenial'
     *
     * @throws InvalidPlatformVersion
     */
    private function getMariaDbMysqlVersionNumber(string $versionString): string
    {
        if (
            1 !== preg_match(
                '/^(?:5\.5\.5-)?(mariadb-)?(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/i',
                $versionString,
                $versionParts,
            )
        ) {
            throw InvalidPlatformVersion::new($versionString, '^(?:5\.5\.5-)?(mariadb-)?<major_version>.<minor_version>.<patch_version>');
        }

        return $versionParts['major'].'.'.$versionParts['minor'].'.'.$versionParts['patch'];
    }

    /**
     * @throws Exception
     */
    public function connect(
        #[\SensitiveParameter]
        array $params,
    ): Connection {
        $driverOptions = $params['driverOptions'] ?? [];

        if (!empty($params['persistent'])) {
            $driverOptions[\PDO::ATTR_PERSISTENT] = true;
        }

        $safeParams = $params;
        unset($safeParams['password'], $safeParams['url']);

        try {
            $pdo = new \PDO(
                $this->constructPdoDsn($safeParams),
                $params['user'] ?? '',
                $params['password'] ?? '',
                $driverOptions,
            );
        } catch (\PDOException $exception) {
            throw Exception::new($exception);
        }

        return new Connection($pdo);
    }

    /**
     * Constructs the MySQL PDO DSN.
     */
    private function constructPdoDsn(array $params): string
    {
        $dsn = 'mysql:';
        if (isset($params['host']) && '' !== $params['host']) {
            $dsn .= 'host='.$params['host'].';';
        }

        if (isset($params['port'])) {
            $dsn .= 'port='.$params['port'].';';
        }

        if (isset($params['dbname'])) {
            $dsn .= 'dbname='.$params['dbname'].';';
        }

        if (isset($params['unix_socket'])) {
            $dsn .= 'unix_socket='.$params['unix_socket'].';';
        }

        if (isset($params['charset'])) {
            $dsn .= 'charset='.$params['charset'].';';
        }

        return $dsn;
    }
}
