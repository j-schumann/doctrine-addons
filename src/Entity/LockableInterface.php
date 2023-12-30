<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Entity;

/**
 * Very simple interface to unify the handling of entities that can be
 * locked / unlocked by admins.
 * It does not force a specific property but allows the entity to decide
 * if e.g. a state field with multiple values is used or a boolean flag.
 * This way the entity can also define context groups etc. individually.
 * It also keeps the overhead low by skipping methods like lock() / unlock()
 * or isLockable() (we assume, the entity can always be locked/unlocked,
 * regardless of other states).
 */
interface LockableInterface
{
    /**
     * Returns true when the entity is currently locked, else false.
     */
    public function isLocked(): bool;

    /**
     * Locks the entity if it's currently unlocked, else unlocks it.
     * Returns the resulting state: true when the entity is now locked, else false.
     */
    public function toggleLocked(): bool;
}
