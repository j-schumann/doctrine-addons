<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\ImportExport;

use PHPUnit\Framework\TestCase;
use Vrok\DoctrineAddons\ImportExport\Helper;
use Vrok\DoctrineAddons\Tests\Fixtures\ExportEntity;
use Vrok\DoctrineAddons\Tests\Fixtures\ImportEntity;

class ExportTest extends TestCase
{
    public function testExportWithGetter(): void
    {
        $helper = new Helper();

        $entity = new ExportEntity();
        $entity->id = 1;
        $entity->setName('test');

        $data = $helper->toArray($entity);

        self::assertSame([
            'id'            => 1,
            'name'          => 'test via getter',
            'collection'    => [],
            'refCollection' => [],
            'parent'        => null,
            'reference'     => null,
            'timestamp'     => null,
            // notExported is NOT in the array
        ], $data);
    }

    public function testPropertyFilter(): void
    {
        $helper = new Helper();

        $entity = new ExportEntity();
        $entity->id = 1;
        $entity->setName('test');

        $data = $helper->toArray($entity, ['name', 'parent']);

        self::assertSame([
            'name'   => 'test via getter',
            'parent' => null,
        ], $data);
    }

    public function testExportDatetime(): void
    {
        $helper = new Helper();

        $now = new \DateTimeImmutable();
        $entity = new ExportEntity();
        $entity->timestamp = $now;

        $data = $helper->toArray($entity);
        self::assertSame($now->format(DATE_ATOM), $data['timestamp']);
    }

    public function testExportCollections(): void
    {
        $helper = new Helper();

        $element1 = new ExportEntity();
        $element1->id = 1;
        $element1->setName('element1');
        $element2 = new ExportEntity();
        $element2->id = 2;
        $element2->setName('element2');

        $refElement1 = new ExportEntity();
        $refElement1->id = 3;
        $refElement1->setName('refElement1');
        $refElement2 = new ExportEntity();
        $refElement2->id = 4;
        $refElement2->setName('refElement2');

        $entity = new ExportEntity();
        $entity->setCollection([$element1, $element2]);
        $entity->setRefCollection([$refElement1, $refElement2]);

        $data = $helper->toArray($entity);
        self::assertSame([
            'id'            => 0,
            'name'          => ' via getter',
            'collection'    => [
                [
                    'id'            => 1,
                    'name'          =>'element1 via getter',
                    'collection'    => [],
                    'refCollection' => [],
                    'parent'        => null,
                    'reference'     => null,
                    'timestamp'     => null,
                    '_entityClass'  => 'Vrok\DoctrineAddons\Tests\Fixtures\ExportEntity',
                ],
                [
                    'id'            => 2,
                    'name'          =>'element2 via getter',
                    'collection'    => [],
                    'refCollection' => [],
                    'parent'        => null,
                    'reference'     => null,
                    'timestamp'     => null,
                    '_entityClass'  => 'Vrok\DoctrineAddons\Tests\Fixtures\ExportEntity',
                ],
            ],
            'refCollection' => [3, 4],
            'parent'        => null,
            'reference'     => null,
            'timestamp'     => null,
        ], $data);
    }

    public function testExportReferences(): void
    {
        $helper = new Helper();

        $parent = new ExportEntity();
        $parent->id = 1;
        $parent->setName('parent');

        $reference = new ExportEntity();
        $reference->id = 2;
        $reference->setName('reference');

        $entity = new ExportEntity();
        $entity->id = 3;
        $entity->setParent($parent);
        $entity->setReference($reference);

        $data = $helper->toArray($entity);
        self::assertSame([
            'id'            => 3,
            'name'          => ' via getter',
            'collection'    => [],
            'refCollection' => [],
            'parent'        => [
                'id'            => 1,
                'name'          =>'parent via getter',
                'collection'    => [],
                'refCollection' => [],
                'parent'        => null,
                'reference'     => null,
                'timestamp'     => null,
            ],
            'reference'     => 'reference via getter',
            'timestamp'     => null,
        ], $data);
    }

    public function testThrowsExceptionWithNonexportableEntity(): void
    {
        $helper = new Helper();
        $entity = new ImportEntity();

        $this->expectException(\RuntimeException::class);
        $helper->toArray($entity);
    }
}
