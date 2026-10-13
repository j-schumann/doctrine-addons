<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\ContainsFunction;
use Vrok\DoctrineAddons\Tests\Fixtures\TestEntity;

final class ContainsFunctionTest extends AbstractPostgresFunctionTestCase
{
    public function testGetSql(): void
    {
        $this->configuration->addCustomStringFunction('CONTAINS', ContainsFunction::class);

        $query = $this->buildEntityManager()->createQuery(
            'SELECT t.id FROM '.TestEntity::class.' t'
            .' WHERE CONTAINS(t.jsonColumn, :para) = true'
        );
        self::assertStringContainsString(
            'WHERE (t0_.jsonColumn @> ?) = ',
            (string) $query->getSQL()
        );
    }

    public function testQueryWithDatabase(): void
    {
        $this->configuration->addCustomStringFunction('CONTAINS', ContainsFunction::class);
        $this->setupRecords();

        $condition = 'CONTAINS(t.jsonColumn, :value) = true';
        self::assertSame(['numbers'], $this->findNames($condition, '5'));
        self::assertSame(['numbers'], $this->findNames($condition, '[1, 5]'));
        self::assertSame([], $this->findNames($condition, '[1, 2]'));
    }
}
