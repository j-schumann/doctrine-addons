<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\ImportExport;

/**
 * Used to mark properties allowed to be imported.
 * Only properties with this attribute are written, even if the data array contains
 * entries for other properties.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ImportableProperty
{
    public ?string $listOf;

    public function __construct($listOf = null)
    {
        $this->listOf = $listOf;
    }
}
