<?php

namespace Vrok\DoctrineAddons\Tests\Entity;

use Vrok\DoctrineAddons\Tests\Fixtures\TestEntity;
use Vrok\DoctrineAddons\Tests\ORM\AbstractOrmTestCase;

final class LockableTraitTest extends AbstractOrmTestCase
{
    public function testToggleLocked(): void
    {
        $entity = new TestEntity();

        self::assertFalse($entity->locked);
        self::assertFalse($entity->isLocked());

        self::assertTrue($entity->toggleLocked());
        self::assertTrue($entity->locked);
        self::assertTrue($entity->isLocked());

        self::assertFalse($entity->toggleLocked());
        self::assertFalse($entity->locked);
        self::assertFalse($entity->isLocked());
    }

    public function testPersistWithDatabase(): void
    {
        $em = $this->buildEntityManager();
        $this->setupSchema();

        $unlocked = new TestEntity();
        $locked = new TestEntity();
        $locked->toggleLocked();
        $em->persist($unlocked);
        $em->persist($locked);
        $em->flush();
        $em->clear();

        $repository = $em->getRepository(TestEntity::class);
        self::assertFalse($repository->find($unlocked->id)?->locked);
        self::assertTrue($repository->find($locked->id)?->locked);
    }
}
