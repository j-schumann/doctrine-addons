<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\JsonContainsAllTextFunction;
use Vrok\DoctrineAddons\Tests\Fixtures\TestEntity;

final class JsonContainsAllTextFunctionTest extends AbstractPostgresFunctionTestCase
{
    public function testGetSql(): void
    {
        $this->configuration->addCustomStringFunction(
            'JSON_CONTAINS_ALL_TEXT',
            JsonContainsAllTextFunction::class
        );

        $query = $this->buildEntityManager()->createQuery(
            'SELECT t.id FROM '.TestEntity::class.' t'
            .' WHERE JSON_CONTAINS_ALL_TEXT(t.jsonColumn, :para) = true'
        );
        self::assertStringContainsString(
            'WHERE (t0_.jsonColumn ??& ARRAY[?]::text[]) = ',
            $query->getSQL()
        );
    }

    public function testQueryWithDatabase(): void
    {
        $this->configuration->addCustomStringFunction(
            'JSON_CONTAINS_ALL_TEXT',
            JsonContainsAllTextFunction::class
        );
        $this->setupRecords();

        $condition = 'JSON_CONTAINS_ALL_TEXT(t.jsonColumn, :value) = true';
        self::assertSame(['roles'], $this->findNames($condition, ['ROLE_ADMIN', 'ROLE_EDITOR']));
        self::assertSame([], $this->findNames($condition, ['ROLE_ADMIN', 'ROLE_ADMIN_BLOG']));
        self::assertSame(['blog'], $this->findNames($condition, 'ROLE_ADMIN_BLOG'));
        self::assertSame(['address', 'blog', 'numbers', 'roles'], $this->findNames($condition, []));
    }
}
