<?php

namespace Vrok\DoctrineAddons\Tests\Entity;

use Symfony\Component\Serializer\Attribute\Groups;
use Vrok\DoctrineAddons\Tests\Fixtures\TestEntity;
use Vrok\DoctrineAddons\Tests\ORM\AbstractOrmTestCase;
use Vrok\ImportExport\ExportableProperty;
use Vrok\ImportExport\ImportableProperty;

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

    public function testLockedPropertyAttributesExist(): void
    {
        $attributes = new \ReflectionProperty(TestEntity::class, 'locked')->getAttributes();

        // newInstance() fails for an attribute whose class does not exist
        $instances = array_map(static fn (\ReflectionAttribute $a): object => $a->newInstance(), $attributes);
        $classes = array_map(get_class(...), $instances);

        self::assertContains(ExportableProperty::class, $classes);
        self::assertContains(ImportableProperty::class, $classes);
        self::assertContains(Groups::class, $classes);
    }
}
