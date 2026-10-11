<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\JsonContainsAnyTextFunction;

final class JsonContainsAnyTextFunctionTest extends AbstractPostgresFunctionTestCase
{
    public function testFunction(): void
    {
        $this->configuration->addCustomStringFunction('JSON_CONTAINS_ANY_TEXT', JsonContainsAnyTextFunction::class);

        $query = $this->buildEntityManager()->createQuery('SELECT t.id FROM Vrok\DoctrineAddons\Tests\Fixtures\TestEntity t WHERE JSON_CONTAINS_ANY_TEXT(t.jsonColumn, :para) = true');
        self::assertStringContainsString('WHERE (t0_.jsonColumn ??| ARRAY[?]::text[]) = ', $query->getSQL());
    }

    public function testWithDatabase(): void
    {
        $this->configuration->addCustomStringFunction('JSON_CONTAINS_ANY_TEXT', JsonContainsAnyTextFunction::class);
        $this->setupRecords();

        $condition = 'JSON_CONTAINS_ANY_TEXT(t.jsonColumn, :value) = true';
        self::assertSame(['blog', 'roles'], $this->findNames($condition, ['ROLE_ADMIN', 'ROLE_ADMIN_BLOG']));
        self::assertSame(['roles'], $this->findNames($condition, ['ROLE_EDITOR', 'ROLE_NONE']));
        self::assertSame([], $this->findNames($condition, ['ROLE']));

        // a single string is treated as an array with one element
        self::assertSame(['blog'], $this->findNames($condition, 'ROLE_ADMIN_BLOG'));

        // an empty array matches no record
        self::assertSame([], $this->findNames($condition, []));
    }
}
