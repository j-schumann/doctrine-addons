<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\ORM;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

abstract class AbstractOrmTestCase extends TestCase
{
    protected Configuration $configuration;

    protected function setUp(): void
    {
        parent::setUp();

        $configuration = new Configuration();
        $configuration->setProxyDir(sys_get_temp_dir());
        $configuration->setProxyNamespace('Tests\Fixtures\Proxies');
        $configuration->setAutoGenerateProxyClasses(true);
        $configuration->setMetadataDriverImpl($configuration->newDefaultAnnotationDriver(
            [__DIR__.'/../Fixtures']
        ));

        $this->configuration = $configuration;
    }

    protected function buildEntityManager(): EntityManager
    {
        return EntityManager::create(['driver' => 'pdo_sqlite', 'memory' => true], $this->configuration);
    }
}
