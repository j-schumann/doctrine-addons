<?php

namespace Vrok\DoctrineAddons\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;

/**
 * Used by the tests that run real queries against the database.
 */
#[ORM\Entity]
class DatabaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 50)]
    public string $name = '';

    // jsonb on Postgres, required by the DQL functions
    #[ORM\Column(
        type: JsonbColumn::TYPE,
        nullable: true,
        options: JsonbColumn::OPTIONS
    )]
    public ?array $jsonColumn = null;

    #[ORM\Column(type: 'small_json', nullable: true)]
    public ?array $smallJson = null;

    #[ORM\Column(type: 'utc_datetime', nullable: true)]
    public ?\DateTimeImmutable $utcDateTime = null;
}
