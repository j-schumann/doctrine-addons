<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\Fixtures;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Vrok\DoctrineAddons\ImportExport\ImportableEntity;
use Vrok\DoctrineAddons\ImportExport\ImportableProperty;

#[ImportableEntity]
class ImportEntity
{
    #[ImportableProperty]
    private ?string $name = '';

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $value): self
    {
        $this->name = null !== $value ? $value.' via setter' : null;

        return $this;
    }

    #[ImportableProperty]
    private Collection $collection;

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    public function setCollection(array $elements): self
    {
        $this->collection->clear();
        foreach ($elements as $element) {
            $this->addToCollection($element);
        }

        return $this;
    }

    public function addToCollection(self $element): self
    {
        if ($this->collection->contains($element)) {
            return $this;
        }

        $this->collection->add($element);

        return $this;
    }

    #[ImportableProperty]
    private ?self $parent = null;

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $value): self
    {
        $this->parent = $value;

        return $this;
    }

    #[ImportableProperty]
    public ?DateTimeImmutable $timestamp = null;

    #[ImportableProperty]
    public ?TestEntity $otherReference = null;

    public string $notImported = 'initial';

    public function __construct()
    {
        $this->collection = new ArrayCollection();
    }
}
