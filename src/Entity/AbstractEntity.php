<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Exception\Write\VariableForInternalNameNotFoundException;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalField;
use NewDavis\DatabaseManagement\Entity\Field\Serializable;
use NewDavis\DatabaseManagement\Entity\Field\StorableInterface;
use NewDavis\DatabaseManagement\Entity\Trait\EntityCreatedAtTrait;
use NewDavis\DatabaseManagement\Entity\Trait\EntityIdTrait;
use NewDavis\DatabaseManagement\Entity\Trait\EntityUpdatedAtTrait;
use Ramsey\Uuid\Uuid;

abstract class AbstractEntity implements EntityInterface
{
    use EntityIdTrait;
    use EntityCreatedAtTrait;
    use EntityUpdatedAtTrait;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function get(Serializable $serializable, string $internalName): mixed
    {
        if (($property = EntityReflectionCache::getProperty($this, $internalName)) == null) {
            throw new VariableForInternalNameNotFoundException($internalName, self::class);
        }

        if (!$property->isInitialized($this)) {
            return $serializable->getSerializer()->encode(
                null
            );
        }

        return $serializable->getSerializer()->encode(
            $property->getValue($this)
        );
    }

    public function set(Serializable $serializable, string $internalName, mixed $value): EntityInterface
    {
        if (($property = EntityReflectionCache::getProperty($this, $internalName)) == null) {
            throw new VariableForInternalNameNotFoundException($internalName, self::class);
        }

        $decoded = $serializable->getSerializer()->decode($value);
        if (
            $decoded instanceof AbstractEntity &&
            ($serializable instanceof ManyToOneRelation || $serializable instanceof OneToOneRelation) &&
            $serializable->getForeignKey() != null
        ) {
            $this->set(
                $serializable->getForeignKey(),
                $serializable->getForeignKey()->getInternalName(),
                $decoded->getId()
            );
        }

        $property->setValue(
            $this,
            $decoded
        );

        return $this;
    }
}
