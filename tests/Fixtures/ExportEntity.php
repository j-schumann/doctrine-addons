<?php

declare(strict_types=1);

namespace Vrok\DoctrineAddons\Tests\Fixtures;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Vrok\DoctrineAddons\ImportExport\ExportableEntity;
use Vrok\DoctrineAddons\ImportExport\ExportableProperty;

#[ExportableEntity]
class ExportEntity
{
    #[ExportableProperty]
    public int $id = 0;

    #[ExportableProperty]
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name.' via getter';
    }

    public function setName(?string $value): self
    {
        $this->name = $value;

        return $this;
    }

    #[ExportableProperty]
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

    #[ExportableProperty(referenceByIdentifier: 'id')]
    private Collection $refCollection;

    public function getRefCollection(): Collection
    {
        return $this->refCollection;
    }

    public function setRefCollection(array $elements): self
    {
        $this->refCollection->clear();
        foreach ($elements as $element) {
            $this->addToRefCollection($element);
        }

        return $this;
    }

    public function addToRefCollection(self $element): self
    {
        if ($this->refCollection->contains($element)) {
            return $this;
        }

        $this->refCollection->add($element);

        return $this;
    }

    #[ExportableProperty]
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

    #[ExportableProperty(referenceByIdentifier: 'name')]
    private ?self $reference = null;

    public function getReference(): ?self
    {
        return $this->reference;
    }

    public function setReference(?self $value): self
    {
        $this->reference = $value;

        return $this;
    }

    #[ExportableProperty]
    public ?DateTimeImmutable $timestamp = null;

    public string $notExported = 'hidden';

    public function __construct()
    {
        $this->collection = new ArrayCollection();
        $this->refCollection = new ArrayCollection();
    }
}
