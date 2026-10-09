<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Vrok\DoctrineAddons\Tests\ORM\Query\AST;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Attributes\Group;
use Vrok\DoctrineAddons\DBAL\Types\SmallJsonType;
use Vrok\DoctrineAddons\DBAL\Types\UTCDateTimeType;
use Vrok\DoctrineAddons\ORM\Query\AST\CastFunction;
use Vrok\DoctrineAddons\ORM\Query\AST\ContainsFunction;
use Vrok\DoctrineAddons\ORM\Query\AST\JsonContainsTextFunction;
use Vrok\DoctrineAddons\ORM\Query\AST\JsonFieldAsTextFunction;
use Vrok\DoctrineAddons\Tests\Fixtures\DatabaseEntity;
use Vrok\DoctrineAddons\Tests\ORM\AbstractOrmTestCase;

/**
 * Runs the Postgres-only DQL functions against a real database, skipped for
 * other databases, see DATABASE_URL.
 */
#[Group('database')]
final class PostgresDqlFunctionsTest extends AbstractOrmTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['small_json' => SmallJsonType::class, 'utc_datetime' => UTCDateTimeType::class] as $name => $class) {
            if (!Type::hasType($name)) {
                Type::addType($name, $class);
            }
        }

        $this->configuration->addCustomStringFunction('CAST', CastFunction::class);
        $this->configuration->addCustomStringFunction('CONTAINS', ContainsFunction::class);
        $this->configuration->addCustomStringFunction('JSON_CONTAINS_TEXT', JsonContainsTextFunction::class);
        $this->configuration->addCustomStringFunction('JSON_FIELD_AS_TEXT', JsonFieldAsTextFunction::class);

        $this->buildDatabaseEntityManager();
        $this->skipUnlessPlatform(PostgreSQLPlatform::class);
        $this->createTables([DatabaseEntity::class]);

        foreach ([
            'roles'   => ['ROLE_ADMIN', 'ROLE_EDITOR'],
            'blog'    => ['ROLE_ADMIN_BLOG'],
            'numbers' => [1, 5],
            'address' => ['city' => 'Dresden', 'zip' => '01069'],
        ] as $name => $json) {
            $record = new DatabaseEntity();
            $record->name = $name;
            $record->jsonColumn = $json;
            $this->em->persist($record);
        }

        $this->em->flush();
    }

    public function testCast(): void
    {
        self::assertSame(['address'], $this->findNames("LOWER(CAST(t.jsonColumn, 'text')) LIKE :value", '%dresden%'));
    }

    public function testContains(): void
    {
        self::assertSame(['numbers'], $this->findNames('CONTAINS(t.jsonColumn, :value) = true', '5'));
        self::assertSame(['numbers'], $this->findNames('CONTAINS(t.jsonColumn, :value) = true', '[1, 5]'));
        self::assertSame([], $this->findNames('CONTAINS(t.jsonColumn, :value) = true', '[1, 2]'));
    }

    public function testJsonContainsText(): void
    {
        // matches only complete elements, not "ROLE_ADMIN_BLOG"
        self::assertSame(['roles'], $this->findNames('JSON_CONTAINS_TEXT(t.jsonColumn, :value) = true', 'ROLE_ADMIN'));
        self::assertSame([], $this->findNames('JSON_CONTAINS_TEXT(t.jsonColumn, :value) = true', 'ROLE'));
    }

    public function testJsonFieldAsText(): void
    {
        self::assertSame(['address'], $this->findNames("JSON_FIELD_AS_TEXT(t.jsonColumn, 'city') = :value", 'Dresden'));
    }

    /**
     * @return list<string> the names of the records matching the condition
     */
    private function findNames(string $condition, mixed $value): array
    {
        $result = $this->em->createQuery(
            'SELECT t.name FROM '.DatabaseEntity::class." t WHERE $condition ORDER BY t.name"
        )
            ->setParameter('value', $value)
            ->getSingleColumnResult();

        return array_values($result);
    }
}
