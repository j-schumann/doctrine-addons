<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Driver;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\PDO\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MySQL57Platform;
use Doctrine\DBAL\Platforms\MySQL80Platform;
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
    /**
     * {@inheritdoc}
     *
     * @throws DBALException
     */
    public function createDatabasePlatformForVersion($version)
    {
        $mariadb = false !== stripos($version, 'mariadb');
        if ($mariadb && version_compare($this->getMariaDbMysqlVersionNumber($version), '10.2.7', '>=')) {
            return new MariadbTestPlatform();
        }

        if (!$mariadb) {
            $oracleMysqlVersion = $this->getOracleMysqlVersionNumber($version);
            if (version_compare($oracleMysqlVersion, '8', '>=')) {
                return new MySQL80Platform();
            }

            if (version_compare($oracleMysqlVersion, '5.7.9', '>=')) {
                return new MySQL57Platform();
            }
        }

        return $this->getDatabasePlatform();
    }

    /**
     * Get a normalized 'version number' from the server string
     * returned by Oracle MySQL servers.
     *
     * @param string $versionString Version string returned by the driver, i.e. '5.7.10'
     *
     * @throws Exception
     */
    private function getOracleMysqlVersionNumber(string $versionString): string
    {
        if (
            0 === preg_match(
                '/^(?P<major>\d+)(?:\.(?P<minor>\d+)(?:\.(?P<patch>\d+))?)?/',
                $versionString,
                $versionParts
            )
        ) {
            throw Exception::invalidPlatformVersionSpecified($versionString, '<major_version>.<minor_version>.<patch_version>');
        }

        $majorVersion = $versionParts['major'];
        $minorVersion = $versionParts['minor'] ?? 0;
        $patchVersion = $versionParts['patch'] ?? null;

        if ('5' === $majorVersion && '7' === $minorVersion && null === $patchVersion) {
            $patchVersion = '9';
        }

        return $majorVersion.'.'.$minorVersion.'.'.$patchVersion;
    }

    /**
     * Detect MariaDB server version, including hack for some mariadb distributions
     * that starts with the prefix '5.5.5-'.
     *
     * @param string $versionString Version string as returned by mariadb server, i.e. '5.5.5-Mariadb-10.0.8-xenial'
     *
     * @throws Exception
     */
    private function getMariaDbMysqlVersionNumber(string $versionString): string
    {
        if (
            0 === preg_match(
                '/^(?:5\.5\.5-)?(mariadb-)?(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/i',
                $versionString,
                $versionParts
            )
        ) {
            throw Exception::invalidPlatformVersionSpecified($versionString, '^(?:5\.5\.5-)?(mariadb-)?<major_version>.<minor_version>.<patch_version>');
        }

        return $versionParts['major'].'.'.$versionParts['minor'].'.'.$versionParts['patch'];
    }

    /**
     * {@inheritdoc}
     *
     * @return Connection
     *
     * @todo remove additional parameters when support for dbal@2.x.x is dropped
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        $driverOptions = $params['driverOptions'] ?? [];

        if (!empty($params['persistent'])) {
            $driverOptions[PDO::ATTR_PERSISTENT] = true;
        }

        return new Connection(
            $this->constructPdoDsn($params),
            $params['user'] ?? '',
            $params['password'] ?? '',
            $driverOptions
        );
    }

    /**
     * Constructs the MySQL PDO DSN.
     *
     * @param mixed[] $params
     *
     * @return string the DSN
     */
    protected function constructPdoDsn(array $params)
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

    /**
     * {@inheritdoc}
     *
     * @deprecated
     *
     * @todo remove when support for dbal@2.x.x is dropped
     */
    public function getName()
    {
        Deprecation::trigger(
            'doctrine/dbal',
            'https://github.com/doctrine/dbal/issues/3580',
            'Driver::getName() is deprecated'
        );

        return 'pdo_mysql';
    }
}
