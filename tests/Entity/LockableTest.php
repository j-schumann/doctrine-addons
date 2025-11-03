<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\Entity\LockableInterface;
use Vrok\DoctrineAddons\Tests\Fixtures\LockableEntity;

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
}
