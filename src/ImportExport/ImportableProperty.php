<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\ImportExport;

use Attribute;

/**
 * Used to mark properties allowed to be imported.
 * Only properties with this attribute are written, even if the data array contains
 * entries for other properties.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ImportableProperty
{
}
