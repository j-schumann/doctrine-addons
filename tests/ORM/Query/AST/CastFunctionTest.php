<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\CastFunction;
use Vrok\DoctrineAddons\Tests\ORM\AbstractOrmTestCase;

class CastFunctionTest extends AbstractOrmTestCase
{
    public function testFunction(): void
    {
        $this->configuration->addCustomStringFunction('CAST', CastFunction::class);

        $query = $this->buildEntityManager()->createQuery('SELECT t.id FROM Vrok\DoctrineAddons\Tests\Fixtures\TestEntity t WHERE CAST(t.jsonColumn, \'text\') = true');
        self::assertSame('SELECT t0_.id AS id_0 FROM TestEntity t0_ WHERE CAST(t0_.jsonColumn as text) = 1', $query->getSQL());
    }
}
