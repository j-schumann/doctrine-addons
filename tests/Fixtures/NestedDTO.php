<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\Fixtures;

use Vrok\DoctrineAddons\ImportExport\ExportableEntity;
use Vrok\DoctrineAddons\ImportExport\ExportableProperty;
use Vrok\DoctrineAddons\ImportExport\ImportableEntity;
use Vrok\DoctrineAddons\ImportExport\ImportableProperty;

#[ExportableEntity]
#[ImportableEntity]
class NestedDTO implements DtoInterface
{
    #[ExportableProperty]
    #[ImportableProperty]
    public string $description = '';

    #[ExportableProperty]
    #[ImportableProperty]
    public int|string $mixedProp = 0;
}
