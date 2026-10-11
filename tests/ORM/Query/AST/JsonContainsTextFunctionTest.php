<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Vrok\DoctrineAddons\ORM\Query\AST\JsonContainsTextFunction;

final class JsonContainsTextFunctionTest extends AbstractPostgresFunctionTestCase
{
    public function testFunction(): void
    {
        $this->configuration->addCustomStringFunction('JSON_EXISTS', JsonContainsTextFunction::class);

        $query = $this->buildEntityManager()->createQuery('SELECT t.id FROM Vrok\DoctrineAddons\Tests\Fixtures\TestEntity t WHERE JSON_EXISTS(t.jsonColumn, :para) = true');
        self::assertStringContainsString('WHERE (t0_.jsonColumn ?? ?) = ', $query->getSQL());
    }

    public function testWithDatabase(): void
    {
        $this->configuration->addCustomStringFunction('JSON_EXISTS', JsonContainsTextFunction::class);
        $this->setupRecords();

        // matches only complete elements, not "ROLE_ADMIN_BLOG"
        self::assertSame(['roles'], $this->findNames('JSON_EXISTS(t.jsonColumn, :value) = true', 'ROLE_ADMIN'));
        self::assertSame([], $this->findNames('JSON_EXISTS(t.jsonColumn, :value) = true', 'ROLE'));
    }
}
