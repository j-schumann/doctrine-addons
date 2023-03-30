<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\ORM;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use PHPUnit\Framework\TestCase;

abstract class AbstractOrmTestCase extends TestCase
{
    protected Configuration $configuration;

    protected function setUp(): void
    {
        parent::setUp();

        $configuration = ORMSetup::createAttributeMetadataConfiguration([__DIR__.'/../Fixtures'], false);
        $configuration->setProxyDir(sys_get_temp_dir());
        $configuration->setProxyNamespace('Tests\Fixtures\Proxies');
        $configuration->setAutoGenerateProxyClasses(true);

        $this->configuration = $configuration;
    }

    protected function buildEntityManager(): EntityManager
    {
        return EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $this->configuration);
    }
}
