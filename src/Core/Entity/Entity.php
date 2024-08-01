<?php

namespace NewDavis\DatabaseManagement\Core\Entity;

use DateTimeImmutable;
use NewDavis\DatabaseManagement\Core\Entity\Trait\CreatedAtTrait;
use NewDavis\DatabaseManagement\Core\Entity\Trait\IdTrait;
use NewDavis\DatabaseManagement\Core\Entity\Trait\UpdatedAtTrait;
use Ramsey\Uuid\Uuid;

class Entity
{

    private bool $persisted;
    private bool $delete = false;

    public function __construct(bool $persisted = false)
    {
        $this->persisted = $persisted;
        $this->id = Uuid::uuid4()->toString();
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();
    }

    use IdTrait;
    use CreatedAtTrait;
    use UpdatedAtTrait;

    public function isPersisted(): bool
    {
        return $this->persisted;
    }

    public function setShouldDelete(bool $shouldDelete): bool
    {
        return $this->delete = $shouldDelete;
    }

    public function shouldDelete(): bool
    {
        return $this->delete;
    }

    public function jsonSerialize(bool $nesting = true, int $nestingDepth = 0, int $maxNestingDepth = 10): array
    {
        $json = [];

        $reflectionClass = new \ReflectionClass($this);

        if($nesting && $nestingDepth < $maxNestingDepth) {
            $nestingDepth++;
        }else{
            $nesting = !$nesting;
        }

        $json['entity'] = $this->convertEntityClassName($this);
        $json['entity_name'] = $this->getDefinitionClass()::ENTITY_NAME;

        foreach ($reflectionClass->getProperties() as $property) {
            $value = null;

            if($property->isInitialized($this)) {
                $value = $property->getValue($this);
            }

            if($value instanceof Entity) {
                if($nesting) {
                    if($value != null) {
                        //relation
                        $value = $value->jsonSerialize($nesting, $nestingDepth, $maxNestingDepth);
                    }

                    $json[$property->getName()] = $value;
                }

                continue;
            } else if($value instanceof EntityCollection) {
                if($nesting) {
                    //relation (collection)
                    $json[$property->getName()] = [];
                    foreach ($value as $entity) {
                        $serialized = $entity->jsonSerialize($nesting, $nestingDepth, $maxNestingDepth);

                        $json[$property->getName()][] = $serialized;
                    }
                }

                continue;
            }

            $json[$property->getName()] = $value;
        }

        unset($json['persisted']);
        unset($json['delete']);

        return $json;
    }

    public static function convertEntityClassName(string|object $class, bool $replaceWithDashes = true) : string
    {
        if($replaceWithDashes) {
            if (is_object($class)) {
                $entityClass = get_class($class);
            } else {
                $entityClass = $class;
            }
            $entityClass = str_replace("\\", '-', $entityClass);
        }

        return $entityClass;
    }

}