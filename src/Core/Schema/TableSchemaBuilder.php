<?php

namespace NewDavis\DatabaseManagement\Core\Schema;

use NewDavis\DatabaseManagement\Core\Entity\Field\Field;
use NewDavis\DatabaseManagement\Core\Entity\Flag\AutoIncrement;
use NewDavis\DatabaseManagement\Core\Entity\Flag\PrimaryKey;
use NewDavis\DatabaseManagement\Core\Entity\Flag\Unique;

class TableSchemaBuilder
{

    public static function build($definitionClass) : string {
        $tableSQL = self::buildTableSQL($definitionClass);
        $flagSQL = self::buildFlagSQL($definitionClass);
        $relationSQL = self::buildRelationSQL($definitionClass);

        return "
            ${tableSQL}
            ${$flagSQL}
            ${relationSQL}
        ";
    }

    public static function buildTableSQL($definitionClass) : string
    {
        $fields = '';
        foreach ($definitionClass::getDefinitionFields() as $field) {
            $fields .= self::convertField($field) . ', ';
        }
        $fields = rtrim($fields, ', ');

        return sprintf(
            'CREATE TABLE IF NOT EXISTS `%s` (%s);',
            $definitionClass::getEntityName(),
            $fields
        );
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

    public static function buildFlagSQL($definitionClass) : string
    {
        // search for MODIFY COLUMN Flags.
        $modifyColumns = '';
        $toModifyFlags = [AutoIncrement::class];
        $modifyFlagsMapping = [];
        foreach ($toModifyFlags as $modifyFlag) {
            $filteredFields = self::filterFieldFlags($definitionClass, [$modifyFlag]);
            foreach ($filteredFields as $field) {
                $modifyFlagsMapping[$field->getStorageName()][] = $modifyFlag;
            }
        }

        // apply the found MODIFY COLUMN flags.
        foreach ($definitionClass::getDefinitionFields() as $field) {
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
            $filteredFields = self::filterFieldFlags($definitionClass, [$extraFlag]);
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
            $definitionClass::getEntityName(),
            $result
        ));
    }

    private static function filterFieldFlags($definitionClass, array $searchedFlags): array
    {
        $filtered = [];

        foreach ($definitionClass::getDefinitionFields() as $field) {
            foreach ($field->getFlags() as $flag) {
                if (in_array(get_class($flag), $searchedFlags)) {
                    $filtered[] = $field;
                    break;
                }
            }
        }

        return $filtered;
    }

    public static function buildRelationSQL($definitionClass) : string
    {
        // TODO add relation build
        return '';
    }

}