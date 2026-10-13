<?php

namespace Vrok\DoctrineAddons\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\Tests\Fixtures\LockableEntity;

final class LockableTraitTest extends TestCase
{
    public function testToggleLocked(): void
    {
        $entity = new LockableEntity();

        self::assertFalse($entity->locked);
        self::assertFalse($entity->isLocked());

        self::assertTrue($entity->toggleLocked());
        self::assertTrue($entity->locked);
        self::assertTrue($entity->isLocked());

        self::assertFalse($entity->toggleLocked());
        self::assertFalse($entity->locked);
        self::assertFalse($entity->isLocked());
    }
}
