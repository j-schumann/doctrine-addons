<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\ContainsFunction;

final class ContainsFunctionTest extends AbstractPostgresFunctionTestCase
{
    public function testFunction(): void
    {
        $this->configuration->addCustomStringFunction('CONTAINS', ContainsFunction::class);

        $query = $this->buildEntityManager()->createQuery(
            'SELECT t.id FROM Vrok\DoctrineAddons\Tests\Fixtures\TestEntity t'
            .' WHERE CONTAINS(t.jsonColumn, :para) = true'
        );
        self::assertStringContainsString('WHERE (t0_.jsonColumn @> ?) = ', $query->getSQL());
    }

    public function testWithDatabase(): void
    {
        $this->configuration->addCustomStringFunction('CONTAINS', ContainsFunction::class);
        $this->setupRecords();

        $condition = 'CONTAINS(t.jsonColumn, :value) = true';
        self::assertSame(['numbers'], $this->findNames($condition, '5'));
        self::assertSame(['numbers'], $this->findNames($condition, '[1, 5]'));
        self::assertSame([], $this->findNames($condition, '[1, 2]'));
    }
}
