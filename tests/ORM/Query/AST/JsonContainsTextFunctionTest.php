<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\JsonContainsTextFunction;
use Vrok\DoctrineAddons\Tests\Fixtures\TestEntity;

final class JsonContainsTextFunctionTest extends AbstractPostgresFunctionTestCase
{
    public function testGetSql(): void
    {
        $this->configuration->addCustomStringFunction(
            'JSON_CONTAINS_TEXT',
            JsonContainsTextFunction::class
        );

        $query = $this->buildEntityManager()->createQuery(
            'SELECT t.id FROM '.TestEntity::class.' t'
            .' WHERE JSON_CONTAINS_TEXT(t.jsonColumn, :para) = true'
        );
        self::assertStringContainsString('WHERE (t0_.jsonColumn ?? ?) = ', $query->getSQL());
    }

    public function testQueryWithDatabase(): void
    {
        $this->configuration->addCustomStringFunction(
            'JSON_CONTAINS_TEXT',
            JsonContainsTextFunction::class
        );
        $this->setupRecords();

        $condition = 'JSON_CONTAINS_TEXT(t.jsonColumn, :value) = true';
        // matches only complete elements, not "ROLE_ADMIN_BLOG"
        self::assertSame(['roles'], $this->findNames($condition, 'ROLE_ADMIN'));
        self::assertSame([], $this->findNames($condition, 'ROLE'));
    }
}
