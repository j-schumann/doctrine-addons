<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\DBAL\Platforms;

// @todo remove this helper when support for DBAL@2.x.x is removed
if (class_exists('\Doctrine\DBAL\Platforms\MySQLPlatform')) {
    class MySQLPlatform extends \Doctrine\DBAL\Platforms\MySQLPlatform
    {
    }
} else {
    class MySQLPlatform extends \Doctrine\DBAL\Platforms\MySqlPlatform
    {
    }
}
