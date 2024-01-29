<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\ImportExport;

use Vrok\DoctrineAddons\ImportExport\Helper;
use Vrok\DoctrineAddons\Tests\Fixtures\ImportEntity;
use Vrok\DoctrineAddons\Tests\Fixtures\TestDTO;
use Vrok\DoctrineAddons\Tests\ORM\AbstractOrmTestCase;

class ImportTest extends AbstractOrmTestCase
{
    public function testImportWithSetter(): void
    {
        $helper = new Helper();

        $data = [
            'name' => 'test',

            // will be ignored and throws no error
            'something' => 'else',
        ];

        $instance = $helper->fromArray($data, ImportEntity::class);

        self::assertInstanceOf(ImportEntity::class, $instance);
        self::assertSame('test via setter', $instance->getName());

        self::assertCount(0, $instance->getCollection());
        self::assertNull($instance->getParent());
        self::assertNull($instance->timestamp);
    }

    public function testImportOfDatetime(): void
    {
        $helper = new Helper();

        $data = [
            'timestamp' => 'tomorrow',
        ];

        $instance = $helper->fromArray($data, ImportEntity::class);

        self::assertInstanceOf(ImportEntity::class, $instance);
        self::assertInstanceOf(\DateTimeImmutable::class, $instance->timestamp);

        $now = new \DateTimeImmutable();
        self::assertGreaterThan($now, $instance->timestamp);
    }

    public function testImportOfReference(): void
    {
        $helper = new Helper();

        $data = [
            'parent' => [
                'name' => 'parentEntity',
            ],
        ];

        $instance = $helper->fromArray($data, ImportEntity::class);

        self::assertInstanceOf(ImportEntity::class, $instance);
        self::assertInstanceOf(ImportEntity::class, $instance->getParent());
        self::assertSame('parentEntity via setter', $instance->getParent()->getName());
    }

    public function testImportOfNull(): void
    {
        $helper = new Helper();

        $data = [
            'name'      => null,
            'parent'    => null,
            'timestamp' => null,
        ];

        $instance = $helper->fromArray($data, ImportEntity::class);
        self::assertInstanceOf(ImportEntity::class, $instance);
        self::assertNull($instance->getParent());
        self::assertNull($instance->getName());
        self::assertNull($instance->timestamp);
    }

    public function testImportIgnoresUnannotatedProperties(): void
    {
        $helper = new Helper();

        $data = [
            'name'        => 'test',
            'notImported' => 'fail!',
        ];

        $instance = $helper->fromArray($data, ImportEntity::class);

        self::assertInstanceOf(ImportEntity::class, $instance);
        self::assertSame('test via setter', $instance->getName());
        self::assertSame('initial', $instance->notImported);
    }

    public function testImportOfReferenceInstance(): void
    {
        $helper = new Helper();

        $parent = new ImportEntity();
        $parent->setName('parent');

        $data = [
            'parent' => $parent,
        ];

        $instance = $helper->fromArray($data, ImportEntity::class);

        self::assertInstanceOf(ImportEntity::class, $instance);
        self::assertInstanceOf(ImportEntity::class, $instance->getParent());
        self::assertSame('parent via setter', $instance->getParent()->getName());

        self::assertSame('', $instance->getName());
        self::assertCount(0, $instance->getCollection());
        self::assertNull($instance->timestamp);
    }

    public function testReferencingExistingRecord(): void
    {
        $em = $this->buildEntityManager();
        $this->setupSchema();

        $parent = new ImportEntity();
        $parent->setName('parent');
        $em->persist($parent);
        $em->flush();
        $em->clear();

        $data = [
            'parent' => $parent->getName(),
        ];

        $helper = new Helper();
        $helper->setObjectManager($em);
        $instance = $helper->fromArray($data, ImportEntity::class);

        self::assertInstanceOf(ImportEntity::class, $instance);
        self::assertInstanceOf(ImportEntity::class, $instance->getParent());
        self::assertSame('parent via setter', $instance->getParent()->getName());

        self::assertSame('', $instance->getName());
        self::assertCount(0, $instance->getCollection());
        self::assertNull($instance->timestamp);
    }

    public function testImportOfCollection(): void
    {
        $helper = new Helper();

        $data = [
            'collection' => [
                [
                    '_entityClass' => ImportEntity::class,
                    'name'         => 'element1',
                ],
                [
                    '_entityClass' => ImportEntity::class,
                    'name'         => 'element2',
                ],
            ],
        ];

        $instance = $helper->fromArray($data, ImportEntity::class);

        self::assertInstanceOf(ImportEntity::class, $instance);
        self::assertCount(2, $instance->getCollection());

        $element1 = $instance->getCollection()[0];
        self::assertInstanceOf(ImportEntity::class, $element1);
        self::assertSame('element1 via setter', $element1->getName());

        $element2 = $instance->getCollection()[1];
        self::assertInstanceOf(ImportEntity::class, $element2);
        self::assertSame('element2 via setter', $element2->getName());

        self::assertSame('', $instance->getName());
        self::assertNull($instance->getParent());
        self::assertNull($instance->timestamp);
    }

    public function testImportOfCollectionWithInstances(): void
    {
        $helper = new Helper();

        $element1 = new ImportEntity();
        $element1->setName('element1');

        $element2 = new ImportEntity();
        $element2->setName('element2');

        $data = [
            'collection' => [$element1, $element2],
        ];

        $instance = $helper->fromArray($data, ImportEntity::class);

        self::assertInstanceOf(ImportEntity::class, $instance);
        self::assertCount(2, $instance->getCollection());

        $collectionElement1 = $instance->getCollection()[0];
        self::assertInstanceOf(ImportEntity::class, $collectionElement1);
        self::assertSame('element1 via setter', $collectionElement1->getName());

        $collectionElement2 = $instance->getCollection()[1];
        self::assertInstanceOf(ImportEntity::class, $collectionElement2);
        self::assertSame('element2 via setter', $collectionElement2->getName());

        self::assertSame('', $instance->getName());
        self::assertNull($instance->getParent());
        self::assertNull($instance->timestamp);
    }

    public function testImportOfList(): void
    {
        $helper = new Helper();

        $data = [
            'list' => [
                [
                    'name' => 'element1',
                ],
                [
                    'name' => 'element2',
                ],
            ],
        ];

        $instance = $helper->fromArray($data, ImportEntity::class);

        self::assertInstanceOf(ImportEntity::class, $instance);
        self::assertCount(2, $instance->list);

        $element1 = $instance->list[0];
        self::assertInstanceOf(TestDTO::class, $element1);
        self::assertSame('element1', $element1->name);

        $element2 = $instance->list[1];
        self::assertInstanceOf(TestDTO::class, $element2);
        self::assertSame('element2', $element2->name);
    }

    public function testImportOfEmptyList(): void
    {
        $helper = new Helper();

        $data = [
            'list' => [],
        ];

        $instance = $helper->fromArray($data, ImportEntity::class);

        self::assertInstanceOf(ImportEntity::class, $instance);
        self::assertCount(0, $instance->list);
    }

    public function testImportOfNullListFails(): void
    {
        $helper = new Helper();

        $data = [
            'list' => null,
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Found NULL for Vrok\DoctrineAddons\Tests\Fixtures\ImportEntity::list, but property is not nullable!");

        $helper->fromArray($data, ImportEntity::class);
    }

    public function testImportOfListWithoutArrayFails(): void
    {
        $helper = new Helper();

        $data = [
            'list' => 'string',
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Property Vrok\DoctrineAddons\Tests\Fixtures\ImportEntity::list is marked as list of 'Vrok\DoctrineAddons\Tests\Fixtures\TestDTO' but it is no array: \"string\"!");
        $helper->fromArray($data, ImportEntity::class);
    }

    public function testImportOfListWithInvalidEntryFails(): void
    {
        $helper = new Helper();

        $data = [
            'list' => [
                'string',
            ],
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Property Vrok\DoctrineAddons\Tests\Fixtures\ImportEntity::list is marked as list of 'Vrok\DoctrineAddons\Tests\Fixtures\TestDTO' but entry is no array: \"string\"!");
        $helper->fromArray($data, ImportEntity::class);
    }

    public function testThrowsExceptionWithoutClassname(): void
    {
        $helper = new Helper();

        $data = [
            'name' => 'test',
        ];

        $this->expectException(\RuntimeException::class);
        $helper->fromArray($data);
    }

    public function testThrowsExceptionWithoutReferenceClassname(): void
    {
        $helper = new Helper();

        $data = [
            'collection' => [
                [
                    'name' => 'element1',
                ],
            ],
        ];

        $this->expectException(\RuntimeException::class);
        $helper->fromArray($data, ImportEntity::class);
    }

    public function testThrowsExceptionForUnannotatedReference(): void
    {
        $helper = new Helper();

        $data = [
            'otherReference' => ['test'],
        ];

        $this->expectException(\RuntimeException::class);
        $helper->fromArray($data);
    }

    public function testThrowsExceptionWithoutObjectManager(): void
    {
        $em = $this->buildEntityManager();
        $this->setupSchema();

        $parent = new ImportEntity();
        $parent->setName('parent');
        $em->persist($parent);
        $em->flush();
        $em->clear();

        $data = [
            'parent' => $parent->getName(),
        ];

        // no objectManager set -> exception when referencing by identifier
        $helper = new Helper();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('objectManager is not set to find object!');
        $helper->fromArray($data, ImportEntity::class);
    }

    public function testThrowsExceptionForAmbiguousUnionType(): void
    {
        $data = [
            'union' => $this,
        ];

        // no objectManager set -> exception when referencing by identifier
        $helper = new Helper();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot import object, found ambiguous union type');
        $helper->fromArray($data, ImportEntity::class);
    }
}
