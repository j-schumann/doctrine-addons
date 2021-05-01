<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Platforms;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;

// @todo remove this helper when support for DBAL@2.x.x is removed
if (InstalledVersions::satisfies(new VersionParser(), 'doctrine/dbal', '^3.0')) {
    class MySQLPlatform extends \Doctrine\DBAL\Platforms\MySQLPlatform
    {
    }
} else {
    class MySQLPlatform extends \Doctrine\DBAL\Platforms\MySqlPlatform
    {
    }
}
