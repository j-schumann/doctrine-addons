<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\JsonFieldAsTextFunction;
use Vrok\DoctrineAddons\Tests\Fixtures\TestEntity;

final class JsonFieldAsTextFunctionTest extends AbstractPostgresFunctionTestCase
{
    public function testGetSql(): void
    {
        $this->configuration->addCustomStringFunction(
            'JSON_FIELD_AS_TEXT',
            JsonFieldAsTextFunction::class
        );

        $query = $this->buildEntityManager()->createQuery(
            'SELECT t.id FROM '.TestEntity::class.' t'
            ." WHERE JSON_FIELD_AS_TEXT(t.jsonColumn, 'city') = 'Dresden'"
        );
        self::assertStringContainsString(
            "WHERE (t0_.jsonColumn->>'city') = 'Dresden'",
            (string) $query->getSQL()
        );
    }

    public function testQueryWithDatabase(): void
    {
        $this->configuration->addCustomStringFunction(
            'JSON_FIELD_AS_TEXT',
            JsonFieldAsTextFunction::class
        );
        $this->setupRecords();

        self::assertSame(
            ['address'],
            $this->findNames("JSON_FIELD_AS_TEXT(t.jsonColumn, 'city') = :value", 'Dresden')
        );
    }
}
