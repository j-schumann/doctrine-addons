<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\Fixtures;

use Vrok\DoctrineAddons\ImportExport\ExportableEntity;
use Vrok\DoctrineAddons\ImportExport\ExportableProperty;
use Vrok\DoctrineAddons\ImportExport\ImportableEntity;
use Vrok\DoctrineAddons\ImportExport\ImportableProperty;

#[ExportableEntity]
#[ImportableEntity]
class TestDTO implements DtoInterface
{
    #[ExportableProperty]
    #[ImportableProperty]
    public string $name = '';

    #[ExportableProperty]
    #[ImportableProperty]
    public ?DtoInterface $nestedInterface = null;

    /**
     * @var array|DtoInterface[]
     */
    #[ExportableProperty]
    #[ImportableProperty(listOf: DtoInterface::class)]
    public array $nestedInterfaceList = [];
}
