<?php

namespace NewDavis\DatabaseManagement\Entity\Read;

use NewDavis\DatabaseManagement\Entity\EntityIdCollection;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\IdField;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\IdFieldSerializer;
use Ramsey\Uuid\Uuid;

class EntityMappingIdResult
{
    private EntityIdCollection $mappingIds;
    /** @var array<string, EntityIdCollection> */
    private array $pairs = [];

    public function __construct(
        private readonly array $keyValuePairs,
        private readonly ManyToManyRelation|OneToManyRelation $relation,
        private readonly EntityReadStatementCollection $statements
    ) {
        $this->mappingIds = new EntityIdCollection();

        $idSerializer = new IdFieldSerializer(new IdField());

        foreach ($this->keyValuePairs as $row) {
            $key = Uuid::fromBytes($row['key'])->toString();
            $value = $idSerializer->decode($row['value']);

            if (!array_key_exists($key, $this->pairs)) {
                $this->pairs[$key] = new EntityIdCollection([]);
            }

            if (!$this->pairs[$key]->has($value)) {
                $this->pairs[$key]->add($value);
            }

            if (!$this->mappingIds->has($value)) {
                $this->mappingIds->add($value);
            }
        }
    }

    /**
     * @return EntityIdCollection
     */
    public function getMappingIds(): EntityIdCollection
    {
        return $this->mappingIds;
    }

    /**
     * @return array
     */
    public function getPairs(): array
    {
        return $this->pairs;
    }

    /**
     * @return ManyToManyRelation|OneToManyRelation
     */
    public function getRelation(): OneToManyRelation|ManyToManyRelation
    {
        return $this->relation;
    }

    /**
     * @return EntityReadStatementCollection
     */
    public function getStatements(): EntityReadStatementCollection
    {
        return $this->statements;
    }
}