<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\Fixtures;

/**
 * @Entity
 */
class TestEntity
{
    /**
     * @Id
     * @Column(type="string")
     * @GeneratedValue
     */
    public string $id = '';

    /**
     * @Column(type="json")
     */
    public array $jsonColumn = [];
}
