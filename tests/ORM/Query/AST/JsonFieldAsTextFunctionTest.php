<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\JsonFieldAsTextFunction;

final class JsonFieldAsTextFunctionTest extends AbstractPostgresFunctionTestCase
{
    public function testFunction(): void
    {
        $this->configuration->addCustomStringFunction(
            'JSON_AS_TEXT',
            JsonFieldAsTextFunction::class
        );

        $query = $this->buildEntityManager()->createQuery(
            'SELECT t.id FROM Vrok\DoctrineAddons\Tests\Fixtures\TestEntity t'
            ." WHERE JSON_AS_TEXT(t.jsonColumn, 'city') = 'Dresden'"
        );
        self::assertStringContainsString(
            "WHERE (t0_.jsonColumn->>'city') = 'Dresden'",
            $query->getSQL()
        );
    }

    public function testWithDatabase(): void
    {
        $this->configuration->addCustomStringFunction(
            'JSON_AS_TEXT',
            JsonFieldAsTextFunction::class
        );
        $this->setupRecords();

        self::assertSame(
            ['address'],
            $this->findNames("JSON_AS_TEXT(t.jsonColumn, 'city') = :value", 'Dresden')
        );
    }
}
