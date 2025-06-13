<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use NewDavis\DatabaseManagement\Core\Entity\Trait\CreatedAtTrait;
use NewDavis\DatabaseManagement\Core\Entity\Trait\IdTrait;
use NewDavis\DatabaseManagement\Core\Entity\Trait\UpdatedAtTrait;
use Ramsey\Uuid\Uuid;

abstract class Entity implements EntityInterface
{

    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new \DateTimeImmutable();
    }

    use IdTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;

    public function jsonSerialize(array &$visited = []): array
    {
        $objectId = spl_object_id($this);
        if (isset($visited[$objectId])) {
            return ['_ref' => $objectId]; // or null / ID / whatever makes sense
        }
        $visited[$objectId] = true;

        $json = [];

        $reflectionClass = new \ReflectionClass($this);
        $json['entity'] = $this->getDefinitionClass()::getEntityName();

        foreach ($reflectionClass->getProperties() as $property) {
            $value = null;

            if ($property->isInitialized($this)) {
                $value = $property->getValue($this);
            }

            if ($value instanceof Entity) {
                $json[$property->getName()] = $value->jsonSerialize($visited);
                continue;
            } elseif ($value instanceof EntityCollection) {
                $json[$property->getName()] = [];
                foreach ($value->getEntities() as $entity) {
                    $json[$property->getName()][] = $entity->jsonSerialize($visited);
                }
                continue;
            }

            $json[$property->getName()] = $value;
        }

        return $json;
    }
}