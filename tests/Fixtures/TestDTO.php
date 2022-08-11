<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\Fixtures;

use Vrok\DoctrineAddons\ImportExport\ExportableEntity;
use Vrok\DoctrineAddons\ImportExport\ExportableProperty;
use Vrok\DoctrineAddons\ImportExport\ImportableEntity;
use Vrok\DoctrineAddons\ImportExport\ImportableProperty;

#[ExportableEntity]
#[ImportableEntity]
class TestDTO
{
    #[ExportableProperty]
    #[ImportableProperty]
    public string $name = '';
}
