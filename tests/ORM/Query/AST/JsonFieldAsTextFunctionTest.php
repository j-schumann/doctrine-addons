<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use PHPUnit\Framework\Attributes\Group;
use Vrok\DoctrineAddons\ORM\Query\AST\JsonFieldAsTextFunction;
use Vrok\DoctrineAddons\Tests\ORM\AbstractOrmTestCase;

final class JsonFieldAsTextFunctionTest extends AbstractOrmTestCase
{
    public function testFunction(): void
    {
        $this->configuration->addCustomStringFunction('JSON_AS_TEXT', JsonFieldAsTextFunction::class);

        $query = $this->buildEntityManager()->createQuery("SELECT t.id FROM Vrok\DoctrineAddons\Tests\Fixtures\TestEntity t WHERE JSON_AS_TEXT(t.jsonColumn, 'city') = 'Dresden'");
        self::assertSame("SELECT t0_.id AS id_0 FROM TestEntity t0_ WHERE (t0_.jsonColumn->>'city') = 'Dresden'", $query->getSQL());
    }

    #[Group('database')]
    public function testWithDatabase(): void
    {
        $this->configuration->addCustomStringFunction('JSON_AS_TEXT', JsonFieldAsTextFunction::class);
        $this->setupPostgresRecords();

        self::assertSame(['address'], $this->findNames("JSON_AS_TEXT(t.jsonColumn, 'city') = :value", 'Dresden'));
    }
}
