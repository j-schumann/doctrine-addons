<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\CastFunction;
use Vrok\DoctrineAddons\Tests\Fixtures\TestEntity;

final class CastFunctionTest extends AbstractPostgresFunctionTestCase
{
    public function testGetSql(): void
    {
        $this->configuration->addCustomStringFunction('CAST', CastFunction::class);

        $query = $this->buildEntityManager()->createQuery(
            'SELECT t.id FROM '.TestEntity::class.' t'
            ." WHERE CAST(t.jsonColumn, 'text') = true"
        );
        self::assertStringContainsString(
            'WHERE CAST(t0_.jsonColumn as text) = ',
            (string) $query->getSQL()
        );
    }

    public function testQueryWithDatabase(): void
    {
        $this->configuration->addCustomStringFunction('CAST', CastFunction::class);
        $this->setupRecords();

        self::assertSame(
            ['address'],
            $this->findNames("LOWER(CAST(t.jsonColumn, 'text')) LIKE :value", '%dresden%')
        );
    }
}
