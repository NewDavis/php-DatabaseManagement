<?php

namespace NewDavis\DatabaseManagement\Core\Search\Criteria\Filter;

use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\ManyToManyRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\OneToManyRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\RelationField;
use NewDavis\DatabaseManagement\Core\Schema\SchemaBuilder;
use NewDavis\DatabaseManagement\Core\Schema\TableSchemaBuilder;
use NewDavis\DatabaseManagement\Core\Search\Criteria\Filter\Exception\UnknownInternalNameException;

abstract class AbstractFilter implements Filter
{

    protected function handleDeepSearch(
        $definition,
        FilterResult $result,
        string $conditionTemplate,
        string ...$parameters
    ) {
        $previousDefinition = $definition;
        $previousJoinName = $definition::getEntityName();
        $explodedInternalName = explode('.', $this->getInternalName());

        for ($depth = 0; $depth < count($explodedInternalName); $depth++) {
            $deepInternalName = $explodedInternalName[$depth];

            if($depth == count($explodedInternalName) - 1) {
                $result->setCondition(sprintf(
                    $conditionTemplate, //"`%s`.`%s` = ?",
                    $previousJoinName,
                    $deepInternalName,
                    ...$parameters
                ));
                break;
            }

            $filteredFields = SchemaBuilder::filterFieldsByInternalName($previousDefinition, $deepInternalName);
            /** @var RelationField $field */
            if (count($filteredFields) == 0) {
                throw new UnknownInternalNameException($previousDefinition, $deepInternalName);
            }

            $field = $filteredFields[0];

            if($field instanceof RelationField) {
                $joinResult = $this->addJoin($field, $previousDefinition, $previousJoinName, $result);

                $previousDefinition = $joinResult['previousDefinition'];
                $previousJoinName = $joinResult['previousJoinName'];
            }
        }
    }

    /**
     * @param RelationField $field
     * @param string $previousDefinition
     * @param string $previousJoinName
     * @param FilterResult $result
     * @return array{previousDefinition: string, previousJoinName: string}
     */
    private function addJoin(
        RelationField $field,
        string $previousDefinition,
        string $previousJoinName,
        FilterResult $result
    ): array {
        $joinTable = $field instanceof ManyToManyRelation ?
            TableSchemaBuilder::createManyToManyTableName(
                $previousDefinition,
                $field->getRelatedToDefinition()
            ) :
            $field->getRelatedToDefinition()::getEntityName();

        switch (get_class($field)) {
            case ManyToManyRelation::class:
                // switched in ManyToMany
                $storageName = $field->getRelatedTo();
                $relatedTo = $previousDefinition::getEntityName() . '_id';
                $joinName = $joinTable;
                break;
            case OneToManyRelation::class:
                $storageName = $field->getRelatedBy();
                $relatedTo = $field->getRelatedTo();
                break;
            default:
                $storageName = $field->getStorageName();
                $relatedTo = $field->getRelatedTo();
                break;
        }

        if(!isset($joinName)) {
            $joinName = sprintf(
                "%s__%s__%s__%s",
                $field->getRelatedToDefinition()::getEntityName(),
                $field->getRelatedTo(),
                $previousDefinition::getEntityName(),
                $storageName
            );
        }

        $result->addJoin(sprintf(
            "LEFT JOIN `%s` AS `%s` ON `%s`.`%s` = `%s`.`%s`",
            $joinTable,
            $joinName,
            $joinName,
            $relatedTo,
            $previousJoinName,
            $storageName
        ));

        if($field instanceof ManyToManyRelation) {
            $previousJoinTable = $joinTable;

            $joinTable = $field->getRelatedToDefinition()::getEntityName();
            $tmpJoinName = sprintf(
                "%s__%s__%s",
                $field->getRelatedToDefinition()::getEntityName(),
                $field->getRelatedTo(),
                $previousJoinTable,
            );

            $join = sprintf(
                "LEFT JOIN `%s` AS `%s` ON `%s`.`%s` = `%s`.`%s`",
                $joinTable,
                $tmpJoinName,
                $tmpJoinName,
                $storageName,
                $joinName,
                $field->getRelatedToDefinition()::getEntityName() . '_id'
            );

            $joinName = $tmpJoinName;

            $result->addJoin($join);
        }

        $previousDefinition = $field->getRelatedToDefinition();
        $previousJoinName = $joinName;

        return [
            'previousDefinition' => $previousDefinition,
            'previousJoinName' => $previousJoinName,
        ];
    }

}