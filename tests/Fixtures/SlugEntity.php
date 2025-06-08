<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
class SlugEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id;

    #[ORM\Column]
    public string $title = '';

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column]
    public ?string $slug = null;
}
