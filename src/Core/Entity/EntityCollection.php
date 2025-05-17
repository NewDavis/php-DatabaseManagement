<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

/**
 * @template TElement
 */
abstract class EntityCollection implements EntityCollectionInterface
{

    /** @var TElement[] */
    private array $entities;

    /**
     * @param array<TElement> $entities
     */
    public function __construct(
        array $entities = []
    ) {
        $this->entities = $entities;
    }

    /**
     * @param TElement[] $entities
     * @return void
     */
    public function set(array $entities): void
    {
        $this->entities = $entities;
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->entities = [];
    }

    /**
     * @param TElement $entity
     * @return void
     */
    public function add($entity): void
    {
        $this->entities[] = $entity;
    }

    /**
     * @param TElement $entity
     * @return void
     */
    public function remove($entity): void
    {
        $this->entities = array_filter($this->entities, fn($item) => $item !== $entity);
    }

    /**
     * @param TElement $entity
     * @return bool
     */
    public function contains($entity): bool
    {
        foreach ($this->entities as $loopEntity) {
            if ($entity->getId() === $loopEntity->getId()) return true;
        }

        return false;
    }

    /**
     * @return TElement|null
     */
    public function first(): ?Entity
    {
        if(count($this->entities) === 0) return null;

        return $this->entities[0];
    }

    /**
     * @return string|null
     */
    public function firstId(): ?string
    {
        if(count($this->entities) === 0) return null;

        return $this->entities[0]->getId();
    }

    /**
     * @return array<string>
     */
    public function getIds()
    {
        return array_map(
            fn($item) => $item->getId(),
            $this->entities
        );
    }

    /*
    /**
     * @param Criteria $criteria
     * @return EntityCollection<TElement>
     */
    /*public function search(Criteria $criteria): EntityCollection
    {
        return $this->createCollectionInstance([]);
    }*/

    /*
    /**
     * @param string $property
     * @param mixed $value
     * @return EntityCollection<TElement>
     */
    /*public function searchBy(string $property, mixed $value): EntityCollection
    {
        return $this->createCollectionInstance([]);
    }*/

    /*
    /**
     * @param $id
     * @return string|null
     */
    /*public function searchId($id): ?string
    {
        return null;
    }*/

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->entities);
    }

    /**
     * @return TElement[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * @param array|null $entities
     * @return EntityCollection|null
     * @throws \ReflectionException
     */
    private function createCollectionInstance(?array $entities): ?EntityCollection
    {
        $reflectionClass = new \ReflectionClass($this);
        if(!$reflectionClass->hasMethod('getDefinitionClass')) return null;

        $method = $reflectionClass->getMethod('getDefinitionClass');

        $definition = (string)$method->invoke($this);
        $collectionClass = $definition::getCollectionClass();

        return new $collectionClass($entities);
    }
}