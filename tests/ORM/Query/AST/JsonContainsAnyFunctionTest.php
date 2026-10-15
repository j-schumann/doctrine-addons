<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\JsonContainsAnyFunction;
use Vrok\DoctrineAddons\Tests\Fixtures\TestEntity;

final class JsonContainsAnyFunctionTest extends AbstractPostgresFunctionTestCase
{
    public function testGetSql(): void
    {
        $this->configuration->addCustomStringFunction(
            'JSON_CONTAINS_ANY',
            JsonContainsAnyFunction::class
        );

        $query = $this->buildEntityManager()->createQuery(
            'SELECT t.id FROM '.TestEntity::class.' t'
            .' WHERE JSON_CONTAINS_ANY(t.jsonColumn, :para) = true'
        );
        self::assertStringContainsString(
            'WHERE (t0_.jsonColumn @> ANY(ARRAY[?]::jsonb[])) = ',
            (string) $query->getSQL()
        );
    }

    public function testQueryWithDatabase(): void
    {
        $this->configuration->addCustomStringFunction(
            'JSON_CONTAINS_ANY',
            JsonContainsAnyFunction::class
        );
        $this->setupRecords();

        $condition = 'JSON_CONTAINS_ANY(t.jsonColumn, :value) = true';
        self::assertSame(
            ['numbers', 'roles'],
            $this->findNames($condition, ['5', '"ROLE_EDITOR"'])
        );
        self::assertSame(['address'], $this->findNames($condition, ['{"city": "Dresden"}', '3']));
        self::assertSame([], $this->findNames($condition, ['"ROLE"', '3']));
        self::assertSame(['numbers'], $this->findNames($condition, '[1, 5]'));
        self::assertSame([], $this->findNames($condition, []));
    }
}
