<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\Fixtures;

use Vrok\DoctrineAddons\Entity\LockableInterface;
use Vrok\DoctrineAddons\Entity\LockableTrait;

class LockableEntity implements LockableInterface
{
    use LockableTrait;
}
