<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\JsonContainsAllTextFunction;

final class JsonContainsAllTextFunctionTest extends AbstractPostgresFunctionTestCase
{
    public function testFunction(): void
    {
        $this->configuration->addCustomStringFunction(
            'JSON_CONTAINS_ALL_TEXT',
            JsonContainsAllTextFunction::class
        );

        $query = $this->buildEntityManager()->createQuery(
            'SELECT t.id FROM Vrok\DoctrineAddons\Tests\Fixtures\TestEntity t'
            .' WHERE JSON_CONTAINS_ALL_TEXT(t.jsonColumn, :para) = true'
        );
        self::assertStringContainsString(
            'WHERE (t0_.jsonColumn ??& ARRAY[?]::text[]) = ',
            $query->getSQL()
        );
    }

    public function testWithDatabase(): void
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
