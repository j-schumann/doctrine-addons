<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\JsonContainsTextFunction;
use Vrok\DoctrineAddons\Tests\ORM\AbstractOrmTestCase;

class JsonContainsTextFunctionTest extends AbstractOrmTestCase
{
    public function testFunction(): void
    {
        $this->configuration->addCustomStringFunction('JSON_EXISTS', JsonContainsTextFunction::class);

        $query = $this->buildEntityManager()->createQuery('SELECT t.id FROM Vrok\DoctrineAddons\Tests\Fixtures\TestEntity t WHERE JSON_EXISTS(t.jsonColumn, :para) = true');
        $this->assertEquals('SELECT t0_.id AS id_0 FROM TestEntity t0_ WHERE (t0_.jsonColumn ?? ?) = 1', $query->getSQL());
    }
}
