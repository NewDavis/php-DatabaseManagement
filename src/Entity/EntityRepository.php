<?php

namespace NewDavis\DatabaseManagement\Entity;

use NewDavis\DatabaseManagement\Entity\Builder\Delete\DeleteBuilder;
use NewDavis\DatabaseManagement\Entity\Builder\Read\Entity\ReadEntityBuilder;
use NewDavis\DatabaseManagement\Entity\Builder\Read\Id\ReadIdBuilder;
use NewDavis\DatabaseManagement\Entity\Builder\Write\WriteAction;
use NewDavis\DatabaseManagement\Entity\Builder\Write\WriteBuilder;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\ManyToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToManyRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\OneToOneRelation;
use NewDavis\DatabaseManagement\Entity\Field\Relational\RelationalField;
use NewDavis\DatabaseManagement\Entity\Field\Scalar\IdField;
use NewDavis\DatabaseManagement\Entity\FieldSerializer\IdFieldSerializer;
use NewDavis\DatabaseManagement\Entity\Read\Criteria\Criteria;
use NewDavis\DatabaseManagement\Entity\Read\EntityIdSearchResult;
use NewDavis\DatabaseManagement\Entity\Read\EntitySearchResult;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteResult;
use NewDavis\DatabaseManagement\Entity\Write\EntityWriteStatementCollection;
use NewDavis\DatabaseManagement\Util\Helper\EntityHelper;
use Ramsey\Uuid\Uuid;
use function Adminer\dump_csv;

class EntityRepository implements EntityRepositoryInterface
{
    private readonly WriteBuilder $writeBuilder;
    private readonly ReadEntityBuilder $readEntityBuilder;
    private readonly ReadIdBuilder $readIdBuilder;
    private readonly DeleteBuilder $deleteBuilder;

    public function __construct(
        private readonly EntityDefinitionInterface $definition,
        private readonly EntityRegistry $registry,
    ) {
        $this->writeBuilder = new WriteBuilder($this->registry, $this->definition);
        $this->readEntityBuilder = new ReadEntityBuilder($this->registry, $this->definition);
        $this->readIdBuilder = new ReadIdBuilder($this->registry, $this->definition);
        $this->deleteBuilder = new DeleteBuilder($this->registry, $this->definition);
    }

    public function search(Criteria $criteria): EntitySearchResult
    {
        $statements = $this->readEntityBuilder->build($criteria);

        $data = $this->registry->getConnection()->query($statements);

        $entities = $this->combineToCollection($data[0]['data']);

        $this->loadRelations($criteria, $entities);

        return new EntitySearchResult(
            $entities,
            $criteria,
            $statements
        );
    }

    public function searchIds(Criteria $criteria): EntityIdSearchResult
    {
        $statements = $this->readIdBuilder->build($criteria);

        $data = $this->registry->getConnection()->query($statements);

        $idCollection = new EntityIdCollection();
        $idSerializer = new IdFieldSerializer(new IdField());

        foreach ($data[0]['data'] as $row) {
            $decodedId = $idSerializer->decode($row['id']);
            $idCollection->add($decodedId);
        }

        return new EntityIdSearchResult(
            $idCollection,
            $criteria,
            $statements
        );
    }

    public function create(AbstractEntity|AbstractEntityCollection|array $entities): EntityWriteResult
    {
        $collection = $this->combineToCollection($entities);

        $writeBuilderResult = $this->writeBuilder->build(WriteAction::CREATE, $collection);
        $combinedQueries = $writeBuilderResult->combineQueries();

        $this->registry->getConnection()->write($combinedQueries);

        return new EntityWriteResult($collection, $combinedQueries);
    }

    public function update(array $entities): EntityWriteResult
    {
        $collection = $this->combineToCollection($entities);

        $writeBuilderResult = $this->writeBuilder->build(WriteAction::UPDATE, $collection, $entities);
        $combinedQueries = $writeBuilderResult->combineQueries();

        $this->registry->getConnection()->write($combinedQueries);

        return new EntityWriteResult($collection, $combinedQueries);
    }

    public function upsert(array $entities): EntityWriteResult
    {
        $collection = $this->combineToCollection($entities);

        $writeBuilderResult = $this->writeBuilder->build(WriteAction::UPSERT, $collection, $entities);
        $combinedQueries = $writeBuilderResult->combineQueries();

        $this->registry->getConnection()->write($combinedQueries);

        return new EntityWriteResult($collection, $combinedQueries);
    }

    public function delete(Criteria $criteria): EntityWriteResult
    {
        $statements = $this->deleteBuilder->build($criteria);

        $this->registry->getConnection()->write($statements);

        return new EntityWriteResult(
            EntityHelper::createCollection($this->definition),
            $statements
        );
    }

    private function loadRelations(Criteria $criteria, EntityCollectionInterface $collection): void
    {
        $relatedIds = [];
        $mappedIds = [];

        /** @var RelationalField $relationField */
        foreach ($this->definition->getFields()->filter(RelationalField::class) as $relationField) {
            if (
                !$relationField->shouldAutoLoad() &&
                // check if criteria has association to load.
                false
            ) {
                continue;
            }

            /** @var AbstractEntity $entity */
            foreach ($collection as $entity) {
                $foundIds = new EntityIdCollection();

                if (
                    $relationField instanceof OneToManyRelation ||
                    $relationField instanceof ManyToManyRelation
                ) {
                    // load relatedids
                } else if (
                    $relationField instanceof OneToOneRelation ||
                    $relationField instanceof ManyToOneRelation
                ) {
                    $foundIds->add(
                        Uuid::fromBytes($entity->get(
                            $relationField->getForeignKey(),
                            $relationField->getForeignKey()->getInternalName()
                        ))
                    );
                }

                if ($foundIds->count() == 0) continue;

                if (!array_key_exists($relationField->getRelatedToDefinition(), $relatedIds)) {
                    $relatedIds[$relationField->getRelatedToDefinition()] = new EntityIdCollection();
                }

                foreach ($foundIds as $foundId) {
                    $relatedIds[$relationField->getRelatedToDefinition()]->add($foundId);
                }
                $mappedIds[$entity->getId()->toString()][$relationField->getInternalName()] = $foundIds;
            }
        }

        dump($relatedIds, $mappedIds);
    }

    protected function combineToCollection(
        AbstractEntity|AbstractEntityCollection|array $entities
    ): AbstractEntityCollection {
        if (is_array($entities)) {
            return $this->getRegistry()->getConverter()->convertArrayToEntityCollection(
                $this->definition,
                $entities
            );
        } else if ($entities instanceof EntityInterface) {
            return EntityHelper::createCollection($this->definition, $entities);
        } else {
            return $entities;
        }
    }

    /**
     * @return EntityDefinitionInterface
     */
    public function getDefinition(): EntityDefinitionInterface
    {
        return $this->definition;
    }

    public function getRegistry(): EntityRegistry
    {
        return $this->registry;
    }

    /**
     * @return WriteBuilder
     */
    public function getWriteBuilder(): WriteBuilder
    {
        return $this->writeBuilder;
    }
}
