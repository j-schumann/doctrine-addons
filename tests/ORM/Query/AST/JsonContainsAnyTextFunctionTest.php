<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\JsonContainsAnyTextFunction;
use Vrok\DoctrineAddons\Tests\ORM\AbstractOrmTestCase;

final class JsonContainsAnyTextFunctionTest extends AbstractOrmTestCase
{
    public function testFunction(): void
    {
        $this->configuration->addCustomStringFunction('JSON_CONTAINS_ANY_TEXT', JsonContainsAnyTextFunction::class);

        $query = $this->buildEntityManager()->createQuery('SELECT t.id FROM Vrok\DoctrineAddons\Tests\Fixtures\TestEntity t WHERE JSON_CONTAINS_ANY_TEXT(t.jsonColumn, :para) = true');
        self::assertSame('SELECT t0_.id AS id_0 FROM TestEntity t0_ WHERE (t0_.jsonColumn ??| ARRAY[?]::text[]) = 1', $query->getSQL());
    }
}
