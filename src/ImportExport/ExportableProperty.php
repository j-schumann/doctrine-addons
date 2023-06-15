<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\ImportExport;

/**
 * Used to mark properties available for export.
 * The argument 'referenceByIdentifier' can be used on properties that reference other
 * entities: Instead of an array only the value of the property named in this argument
 * is returned, e.g. the ID.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ExportableProperty
{
    public ?string $referenceByIdentifier;

    public function __construct($referenceByIdentifier = null)
    {
        $this->referenceByIdentifier = $referenceByIdentifier;
    }
}
