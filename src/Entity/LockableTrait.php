<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Vrok\DoctrineAddons\ImportExport\ExportableProperty;
use Vrok\DoctrineAddons\ImportExport\ImportableProperty;

/**
 * This trait represents an opinionated implementation of the LockableInterface.
 * It is used to unify the handling across all entities by making a few
 * simplifications:
 * 1) Each entity has a "locked" flag instead of differently named booleans
 * (like $user->active) or combining the locking with other states, as this would
 * prevent a transition to workflows and always poses the challenge: in which state
 * should the entity be switched when unlocking?
 * 2) The flag is always visible when the entity is returned (e.g. in an API
 * response). If unwanted, the property could be overwritten in the using class,
 * or it could be removed by a normalizer.
 */
trait LockableTrait
{
    #[ExportableProperty]
    #[ImportableProperty]
    #[Groups(['default:read'])]
    #[ORM\Column(options: ['default' => false])]
    public bool $locked = false;

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function toggleLocked(): bool
    {
        $this->locked = !$this->locked;

        return $this->locked;
    }
}
