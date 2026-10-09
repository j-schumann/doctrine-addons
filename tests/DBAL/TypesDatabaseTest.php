<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Vrok\DoctrineAddons\Tests\DBAL;

use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Attributes\Group;
use Vrok\DoctrineAddons\DBAL\Types\SmallJsonType;
use Vrok\DoctrineAddons\DBAL\Types\UTCDateTimeType;
use Vrok\DoctrineAddons\Tests\Fixtures\DatabaseEntity;
use Vrok\DoctrineAddons\Tests\ORM\AbstractOrmTestCase;

/**
 * Stores and loads the custom types with the database given in DATABASE_URL.
 */
#[Group('database')]
final class TypesDatabaseTest extends AbstractOrmTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['small_json' => SmallJsonType::class, 'utc_datetime' => UTCDateTimeType::class] as $name => $class) {
            if (!Type::hasType($name)) {
                Type::addType($name, $class);
            }
        }

        $this->buildDatabaseEntityManager();
        $this->createTables([DatabaseEntity::class]);
    }

    public function testSmallJsonRoundTrip(): void
    {
        $data = ['text' => 'Grüße', 'float' => 1.0, 'list' => [1, 2], 'null' => null];

        $id = $this->persist(static function (DatabaseEntity $record) use ($data): void {
            $record->smallJson = $data;
        });

        self::assertSame($data, $this->em->find(DatabaseEntity::class, $id)->smallJson);
    }

    public function testUtcDateTimeRoundTrip(): void
    {
        $local = new \DateTimeImmutable('2026-10-09 18:30:15', new \DateTimeZone('Europe/Berlin'));

        $id = $this->persist(static function (DatabaseEntity $record) use ($local): void {
            $record->utcDateTime = $local;
        });

        // stored as UTC
        $stored = $this->em->getConnection()->fetchOne(
            'SELECT '.$this->column('utcDateTime').' FROM '.$this->table().' WHERE id = ?',
            [$id]
        );
        self::assertStringStartsWith('2026-10-09 16:30:15', $stored);

        $loaded = $this->em->find(DatabaseEntity::class, $id)->utcDateTime;
        self::assertSame('UTC', $loaded->getTimezone()->getName());
        self::assertSame($local->getTimestamp(), $loaded->getTimestamp());
    }

    /**
     * @param callable(DatabaseEntity): void $setValues
     */
    private function persist(callable $setValues): int
    {
        $record = new DatabaseEntity();
        $record->name = 'test';
        $setValues($record);

        $this->em->persist($record);
        $this->em->flush();
        $this->em->clear();

        return $record->id;
    }

    private function table(): string
    {
        return $this->em->getClassMetadata(DatabaseEntity::class)->getTableName();
    }

    private function column(string $property): string
    {
        return $this->em->getClassMetadata(DatabaseEntity::class)->getColumnName($property);
    }
}
