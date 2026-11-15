<?php

namespace Vrok\DoctrineAddons\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
class SlugEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column]
    public string $title = '';

    // the SluggableListener only fills a NULL slug
    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(nullable: true)]
    public ?string $slug = null;
}
