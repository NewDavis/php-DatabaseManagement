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

    public function jsonSerialize(bool $nesting = true, int $nestingDepth = 0, int $maxNestingDepth = 10): array
    {
        $json = [];

        $reflectionClass = new \ReflectionClass($this);

        if($nesting && $nestingDepth < $maxNestingDepth) {
            $nestingDepth++;
        }else{
            $nesting = !$nesting;
        }

        $json['entity'] = $this->getDefinitionClass()::getEntityName();

        foreach ($reflectionClass->getProperties() as $property) {
            $value = null;

            if($property->isInitialized($this)) {
                $value = $property->getValue($this);
            }

            if ($value instanceof Entity) {
                if($nesting) {
                    $json[$property->getName()] = $value->jsonSerialize($nesting, $nestingDepth, $maxNestingDepth);
                }

                continue;
            } else if ($value instanceof EntityCollection) {
                if($nesting) {
                    $json[$property->getName()] = [];
                    foreach ($value->getEntities() as $entity) {
                        $json[$property->getName()][] = $entity->jsonSerialize(
                            $nesting,
                            $nestingDepth,
                            $maxNestingDepth
                        );
                    }
                }

                continue;
            }

            $json[$property->getName()] = $value;
        }

        return $json;
    }
}