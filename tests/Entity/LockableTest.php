<?php

namespace Vrok\DoctrineAddons\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Attribute\Groups;
use Vrok\DoctrineAddons\Entity\LockableInterface;
use Vrok\DoctrineAddons\Tests\Fixtures\LockableEntity;
use Vrok\ImportExport\ExportableProperty;
use Vrok\ImportExport\ImportableProperty;

final class LockableTest extends TestCase
{
    public function testLockableInterface(): void
    {
        $entity = new LockableEntity();

        self::assertInstanceOf(LockableInterface::class, $entity);

        $oldState = $entity->isLocked();
        $result = $entity->toggleLocked();
        self::assertSame(!$oldState, $result);

        $newState = $entity->isLocked();
        self::assertSame($result, $newState);
    }

    public function testLockableTrait(): void
    {
        $entity = new LockableEntity();

        // initial state
        self::assertFalse($entity->locked);
        self::assertFalse($entity->isLocked());

        // lock entity
        self::assertTrue($entity->toggleLocked());
        self::assertTrue($entity->locked);
        self::assertTrue($entity->isLocked());

        // unlock entity
        self::assertFalse($entity->toggleLocked());
        self::assertFalse($entity->locked);
        self::assertFalse($entity->isLocked());
    }

    public function testLockedPropertyAttributesExist(): void
    {
        $attributes = new \ReflectionProperty(LockableEntity::class, 'locked')->getAttributes();

        // newInstance() fails for an attribute whose class does not exist
        $instances = array_map(static fn (\ReflectionAttribute $a): object => $a->newInstance(), $attributes);
        $classes = array_map(get_class(...), $instances);

        self::assertContains(ExportableProperty::class, $classes);
        self::assertContains(ImportableProperty::class, $classes);
        self::assertContains(Groups::class, $classes);
    }
}
