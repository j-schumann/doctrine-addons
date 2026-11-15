<?php

// Loads the entity manager for phpstan/phpstan-doctrine

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

$configuration = ORMSetup::createAttributeMetadataConfig([__DIR__.'/Fixtures'], true);
$configuration->enableNativeLazyObjects(true);

return new EntityManager(
    DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $configuration),
    $configuration
);
