<?php

namespace NewDavis\DatabaseManagement\Core\Schema;

use NewDavis\DatabaseManagement\Core\Entity\Exception\UnableToFindMatchingRelationFieldForFkFieldException;
use NewDavis\DatabaseManagement\Core\Entity\Field\Field;
use NewDavis\DatabaseManagement\Core\Entity\Field\FkField;
use NewDavis\DatabaseManagement\Core\Entity\Field\IdField;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\ManyToManyRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\ManyToOneRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\OneToOneRelation;
use NewDavis\DatabaseManagement\Core\Entity\Field\Relation\RelationField;
use NewDavis\DatabaseManagement\Core\Entity\Flag\AutoIncrement;
use NewDavis\DatabaseManagement\Core\Entity\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Unique;

class TableSchemaBuilder
{

    public static function build($definition) : array {
        $tableSQL = self::createTable($definition);
        $flagSQL = self::setFlags($definition::getEntityName(), $definition, $definition::getDefinitionFields());
        $relationSQL = self::addRelations($definition);

        return [
            $tableSQL,
            $flagSQL,
            $relationSQL,
        ];
    }

    public static function createTable($definition) : string
    {
        $fields = '';
        foreach ($definition::getDefinitionFields() as $field) {
            if($field instanceof RelationField) continue;

            $fields .= self::convertField($field) . ', ';
        }
        $fields = rtrim($fields, ', ');

        return sprintf(
            'CREATE TABLE IF NOT EXISTS `%s` (%s);',
            $definition::getEntityName(),
            $fields
        );
    }

    public static function createManyToManyTables($definition): array
    {
        $queries = [];

        foreach ($definition::getDefinitionFields() as $field) {
            if(!($field instanceof ManyToManyRelation)) continue;

            $manyToManyTableName = self::createManyToManyTableName(
                $definition::getEntityName(),
                $field->getRelatedToDefinition()::getEntityName()
            );

            $byField = new IdField(
                $field->getRelatedByInternalName(),
                sprintf(
                    "%s_id",
                    $definition::getEntityName()
                )
            );
            $toField = new IdField(
                $field->getRelatedToInternalName(),
                sprintf(
                    "%s_id",
                    $field->getRelatedToDefinition()::getEntityName()
                )
            );

            // create table
            $queries[] = sprintf(
                "CREATE TABLE IF NOT EXISTS `%s` (%s, %s);",
                $manyToManyTableName,
                self::convertField($byField),
                self::convertField($toField)
            );

            // add Primary Keys
            $queries[] = self::setFlags(
                $manyToManyTableName,
                $definition,
                [
                    $byField,
                    $toField
                ]
            );

            $queries[] = self::addManyToManyRelations(
                $definition,
                $manyToManyTableName,
                $field,
                $byField,
                $toField
            );
        }

        return $queries;
    }

    public static function createManyToManyTableName(string $entityA, string $entityB)
    {
        // Kleinschreibung und sortieren für Konsistenz
        $entities = [strtolower($entityA), strtolower($entityB)];
        sort($entities);

        // Optional: Snake-Case erzwingen, falls nötig
        // z.B. convert `UserRole` zu `user_role`
        $entities = array_map(function ($name) {
            return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
        }, $entities);

        return implode('_', $entities);
    }

    private static function addManyToManyRelations(
        $definition,
        string $manyToManyTableName,
        ManyToManyRelation $relationField,
        Field $byField,
        Field $toField
    ) : string {
        $constraints = [];

        $foreignKey = sprintf('FK_%s', $byField->getStorageName());

        $constraints[] = sprintf(
            'ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES %s(`%s`) ON DELETE CASCADE',
            $foreignKey,
            $byField->getStorageName(),
            $definition::getEntityName(),
            $relationField->getRelatedByInternalName(),
        );

        $foreignKey = sprintf('FK_%s', $toField->getStorageName());

        $constraints[] = sprintf(
            'ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES %s(`%s`) ON DELETE CASCADE',
            $foreignKey,
            $toField->getStorageName(),
            $relationField->getRelatedToDefinition()::getEntityName(),
            $relationField->getRelatedByInternalName(),
        );

        return rtrim(sprintf(
            "ALTER TABLE `%s` %s;",
            $manyToManyTableName,
            implode(', ', $constraints)
        ));
    }

    public static function setFlags(string $entityName, string $definition, array $fields) : string
    {
        // search for all relationFields
        $relationFields = array_filter(
            $fields,
            fn($field) => ($field instanceof RelationField)
        );

        // check for FkFields and set default flags for specific relation type.
        foreach ($fields as $field) {
            if(!($field instanceof FkField)) continue;

            $matchingRelation = self::findMatchingRelationField($field, $relationFields);
            if(!$matchingRelation) {
                throw new UnableToFindMatchingRelationFieldForFkFieldException(
                    $field->getStorageName(),
                    $definition
                );
            }

            switch (get_class($matchingRelation)) {
                case OneToOneRelation::class:
                    $field->addFlag(new Unique());
                    break;
                case ManyToOneRelation::class:
                    $field->addFlag(new PrimaryKey());
                    break;
            }
        }

        $fields = array_filter(
            $fields,
            fn($field) => !($field instanceof RelationField)
        );

        // search for MODIFY COLUMN Flags.
        $modifyColumns = '';
        $toModifyFlags = [AutoIncrement::class];
        $modifyFlagsMapping = [];
        foreach ($toModifyFlags as $modifyFlag) {
            $filteredFields = self::filterFieldFlags($fields, [$modifyFlag]);
            foreach ($filteredFields as $field) {
                $modifyFlagsMapping[$field->getStorageName()][] = $modifyFlag;
            }
        }

        // apply the found MODIFY COLUMN flags.
        foreach ($fields as $field) {
            if(array_key_exists($field->getStorageName(), $modifyFlagsMapping)) {
                $modifyFlags = $modifyFlagsMapping[$field->getStorageName()];
                $modifyFlagsKeywords = array_map(
                    fn($flag) => $flag::getKeyword(),
                    $modifyFlags
                );

                $modifyColumns .= sprintf(
                    "MODIFY COLUMN %s %s, ",
                    self::convertField($field),
                    implode(' ', $modifyFlagsKeywords)
                );
            }
        }

        $result = $modifyColumns;

        // search and apply ADD PRIMARY KEY or ADD UNIQUE Flags.
        $extraFlags = [PrimaryKey::class, Unique::class];
        foreach ($extraFlags as $extraFlag) {
            $filteredFields = self::filterFieldFlags($fields, [$extraFlag]);
            $storageNames = array_map(
                fn($field) => '`' . $field->getStorageName() . '`',
                $filteredFields
            );
            $result .= sprintf(
                count($filteredFields) > 0 ? $extraFlag::getKeyword() . ', ' : '',
                implode(', ', $storageNames)
            );
        }

        $result = rtrim($result, ', ');

        return rtrim(sprintf(
            "ALTER TABLE `%s` %s;",
            $entityName,
            $result
        ));
    }

    public static function addRelations($definition) : string
    {
        $constraints = [];

        $relationFields = array_filter(
            $definition::getDefinitionFields(),
            fn($field) => ($field instanceof RelationField)
        );

        foreach ($definition::getDefinitionFields() as $field) {
            if(!($field instanceof FkField)) continue;
            $matchingRelation = self::findMatchingRelationField($field, $relationFields);
            if(!$matchingRelation) {
                throw new UnableToFindMatchingRelationFieldForFkFieldException(
                    $field->getStorageName(),
                    $definition
                );
            }

            $foreignKey = sprintf('FK_%s', $field->getStorageName());
            // TODO add possibility to add ON DELETE.
            $onDelete = null;
            $onUpdate = null;

            if($foreignKey) {
                $constraints[] = sprintf(
                    "ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES %s(`%s`)%s%s",
                    $foreignKey,
                    $field->getStorageName(),
                    $field->getRelatedDefinitionClass()::getEntityName(),
                    $matchingRelation->getRelatedToInternalName(),
                    $onDelete ?? '',
                    $onUpdate ?? ''
                );
            }
        }

        return rtrim(sprintf(
            "ALTER TABLE `%s` %s;",
            $definition::getEntityName(),
            implode(', ', $constraints)
        ));
    }

    private static function findMatchingRelationField(FkField $fkField, array $fields) : ?RelationField
    {
        $relationField = null;

        /** @var RelationField $field */
        foreach ($fields as $field) {
            if($field->getStorageName() === $fkField->getStorageName() &&
                $field->getRelatedToDefinition() === $fkField->getRelatedDefinitionClass()) {
                $relationField = $field;
            }
        }

        return $relationField;
    }

    private static function convertField(Field $field): string
    {
        $storageName = $field->getStorageName();
        $type = $field->getType();
        $length = $field->getLength();

        $inlineFlags = '';
        foreach ($field->getFlags() as $flag) {
            if(!$flag->isInline()) continue;

            $inlineFlags .= $flag->getKeyword() . ' ';
        }
        $inlineFlags = rtrim($inlineFlags, ' ');

        return sprintf(
            "`%s` %s%s %s",
            $storageName,
            $type,
            ($length != -1 ? '(' . $length . ')' : ''),
            $inlineFlags
        );
    }

    private static function filterFieldFlags(array $fields, array $searchedFlags): array
    {
        $filtered = [];

        foreach ($fields as $field) {
            foreach ($field->getFlags() as $flag) {
                if (in_array(get_class($flag), $searchedFlags)) {
                    $filtered[] = $field;
                    break;
                }
            }
        }

        return $filtered;
    }

}