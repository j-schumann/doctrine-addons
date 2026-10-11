<?php

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use PHPUnit\Framework\Attributes\Group;
use Vrok\DoctrineAddons\ORM\Query\AST\ContainsFunction;

final class ContainsFunctionTest extends AbstractPostgresFunctionTestCase
{
    public function testFunction(): void
    {
        $this->configuration->addCustomStringFunction('CONTAINS', ContainsFunction::class);

        $query = $this->buildEntityManager()->createQuery('SELECT t.id FROM Vrok\DoctrineAddons\Tests\Fixtures\TestEntity t WHERE CONTAINS(t.jsonColumn, :para) = true');
        self::assertStringContainsString('WHERE (t0_.jsonColumn @> ?) = ', $query->getSQL());
    }

    #[Group('database')]
    public function testWithDatabase(): void
    {
        $this->configuration->addCustomStringFunction('CONTAINS', ContainsFunction::class);
        $this->setupRecords();

        self::assertSame(['numbers'], $this->findNames('CONTAINS(t.jsonColumn, :value) = true', '5'));
        self::assertSame(['numbers'], $this->findNames('CONTAINS(t.jsonColumn, :value) = true', '[1, 5]'));
        self::assertSame([], $this->findNames('CONTAINS(t.jsonColumn, :value) = true', '[1, 2]'));
    }
}
