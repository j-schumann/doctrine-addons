<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\CastFunction;

final class CastFunctionTest extends AbstractPostgresFunctionTestCase
{
    public function testFunction(): void
    {
        $this->configuration->addCustomStringFunction('CAST', CastFunction::class);

        $query = $this->buildEntityManager()->createQuery('SELECT t.id FROM Vrok\DoctrineAddons\Tests\Fixtures\TestEntity t WHERE CAST(t.jsonColumn, \'text\') = true');
        self::assertStringContainsString('WHERE CAST(t0_.jsonColumn as text) = ', $query->getSQL());
    }

    public function testWithDatabase(): void
    {
        $this->configuration->addCustomStringFunction('CAST', CastFunction::class);
        $this->setupRecords();

        self::assertSame(['address'], $this->findNames("LOWER(CAST(t.jsonColumn, 'text')) LIKE :value", '%dresden%'));
    }
}
