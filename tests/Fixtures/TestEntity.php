<?php

namespace Vrok\DoctrineAddons\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TestEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 50)]
    public string $name = '';

    /**
     * jsonb on Postgres, required by the DQL functions.
     */
    #[ORM\Column(type: JsonbColumn::TYPE, options: JsonbColumn::OPTIONS)]
    public array $jsonColumn = [];
}
