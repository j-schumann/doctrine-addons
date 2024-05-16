<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\ContainsFunction;
use Vrok\DoctrineAddons\Tests\ORM\AbstractOrmTestCase;

class ContainsFunctionTest extends AbstractOrmTestCase
{
    public function testFunction(): void
    {
        $this->configuration->addCustomStringFunction('CONTAINS', ContainsFunction::class);

        $query = $this->buildEntityManager()->createQuery('SELECT t.id FROM Vrok\DoctrineAddons\Tests\Fixtures\TestEntity t WHERE CONTAINS(t.jsonColumn, :para) = true');
        $this->assertEquals('SELECT t0_.id AS id_0 FROM TestEntity t0_ WHERE (t0_.jsonColumn @> ?) = 1', $query->getSQL());
    }
}
