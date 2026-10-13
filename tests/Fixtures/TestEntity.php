<?php

namespace Vrok\DoctrineAddons\Tests\Fixtures;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Vrok\DoctrineAddons\Entity\LockableInterface;
use Vrok\DoctrineAddons\Entity\LockableTrait;

#[ORM\Entity]
class TestEntity implements LockableInterface
{
    use LockableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 50)]
    public string $name = '';

    /**
     * jsonb on Postgres, required by the DQL functions.
     */
    #[ORM\Column(type: Types::JSONB)]
    public array $jsonColumn = [];
}
