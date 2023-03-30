<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\Fixtures;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TestEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public string $id = '';

    #[ORM\Column(type: Types::JSON)]
    public array $jsonColumn = [];
}
