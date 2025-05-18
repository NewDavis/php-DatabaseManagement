<?php

namespace NewDavis\DatabaseManagement\Core\Search\Association;

use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\ManyToManyRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\ManyToOneRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\OneToManyRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\OneToOneRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\RelationField;
use NewDavis\DatabaseManagement\Core\Schema\TableSchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Criteria;

class Association
{

    public function __construct(
        private readonly RelationField $field,
        private $definition,
        private readonly Criteria $criteria,
        private readonly int $depth
    ) {
    }

    public function convert() : string|null
    {
        if(!$this->shouldLoadAssociation()) return null;

        $join = null;
        $joinTable = null;

        $relatedTable = $this->field->getRelatedToDefinition()::getEntityName();
        $relatedBy = $this->field instanceof OneToManyRelation ?
            $this->field->getRelatedBy() :
            $this->field->getStorageName();
        $relatedTo = $this->field->getRelatedTo();

        switch (get_class($this->field)) {
            case OneToOneRelation::class:
            case ManyToOneRelation::class:
                $relatedTable = $this->field->getRelatedToDefinition()::getEntityName();
                $relatedBy = $this->field->getStorageName();
                $relatedTo = $this->field->getRelatedTo();
                break;
            case ManyToManyRelation::class:
                $relatedTable = TableSchemaBuilder::createManyToManyTableName(
                    $this->definition,
                    $this->field->getRelatedToDefinition()
                );

                $relatedBy = $this->field->getRelatedBy();
                $relatedTo = $this->definition::getEntityName() . '_id';

                $joinTable = $this->field->getRelatedToDefinition()::getEntityName();

                $join = sprintf(
                    "JOIN `%s` ON `%s`.`%s` = `%s`.`%s`",
                    $joinTable,
                    $relatedTable,
                    $this->field->getRelatedToDefinition()::getEntityName() . '_id',
                    $joinTable,
                    $this->field->getRelatedTo()
                );
                break;
            case OneToManyRelation::class:
                $relatedTable = $this->field->getRelatedToDefinition()::getEntityName();
                $relatedBy = $this->field->getRelatedBy();
                $relatedTo = $this->field->getRelatedTo();
                break;
        }

        $fields = array_map(
            function ($relatedDefinitionField) use ($relatedTable, $joinTable) {
                $prefix = "'" . $relatedDefinitionField->getInternalName() . "', ";

                if($relatedDefinitionField instanceof RelationField) {
                    $converted = (new Association(
                        $relatedDefinitionField,
                        $this->field->getRelatedToDefinition(),
                        $this->getRemainingAssociations(),
                        $this->depth + 1
                    ))->convert();

                    if(!$converted) return null;

                    return $prefix . $converted;
                }

                return sprintf(
                    "%s`%s`.`%s`",
                    $prefix,
                    $joinTable ?? $relatedTable,
                    $relatedDefinitionField->getStorageName()
                );
            },
            $this->field->getRelatedToDefinition()::getDefinitionFields()
        );

        $fields = array_filter(
            $fields,
            fn($field) => $field
        );

        $association = sprintf(
            "(
  SELECT JSON_ARRAYAGG(
      JSON_OBJECT(
          %s
      )
  )
  FROM `%s`
  %s
  WHERE `%s`.`%s` = `%s`.`%s`
) %s",
            // fields
            implode(", ", $fields),
            // table
            $relatedTable,
            $join ?? '',
            $relatedTable,
            $relatedTo,
            $this->definition::getEntityName(),
            $relatedBy,
            $this->depth == 0 ? 'AS ' . $this->field->getInternalName() : ''
        );

        return $association;
    }

    private function shouldLoadAssociation(): bool {
        $shouldLoad = false;

        if($this->field->isAutoload()) return true;

        foreach ($this->criteria->getAssociations() as $association) {
            $explodedAssociation = explode('.', $association);

            if (count($explodedAssociation) <= $this->depth) continue;

            if ($explodedAssociation[$this->depth] === $this->field->getInternalName()) {
                $shouldLoad = true;
            }
        }

        if (!$shouldLoad) return false;

        return true;
    }

    private function getRemainingAssociations(): Criteria {
        $criteria = new Criteria();

        foreach ($this->criteria->getAssociations() as $association) {
            $explodedAssociation = explode('.', $association);

            if (count($explodedAssociation) <= $this->depth) continue;

            $associationDepth = $explodedAssociation[$this->depth];

            if($associationDepth != $this->field->getInternalName()) continue;

            $criteria->addAssociation($association);
        }

        return $criteria;
    }

}