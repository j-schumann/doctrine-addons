<?php

namespace Vrok\DoctrineAddons\Entity;

/**
 * Unifies the handling of entities that can be locked / unlocked by admins. It does not force a
 * specific property but allows the entity to decide if e.g. a state field with multiple values is
 * used or a boolean flag. This way the entity can also define context groups etc. individually. It
 * also keeps the overhead low by skipping methods like lock() / unlock() or isLockable() (we assume
 * the entity can always be locked/unlocked, regardless of other states).
 */
interface LockableInterface
{
    public function isLocked(): bool;

    /**
     * Toggles the lock state and returns the new state, true when the entity is now locked.
     */
    public function toggleLocked(): bool;
}
