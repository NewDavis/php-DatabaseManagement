<?php

namespace DatabaseManagement\Schema;

use DatabaseManagement\Core\Entity\Entity;
use DatabaseManagement\Core\Entity\EntityDefinition;
use DatabaseManagement\Core\Entity\EntityRepository;
use DatabaseManagement\Core\Entity\Property\Flags\AutoIncrement;
use DatabaseManagement\Core\Entity\Property\Flags\Nullable;
use DatabaseManagement\Core\Entity\Property\Flags\PrimaryKey;
use DatabaseManagement\Core\Entity\Property\Flags\Required;
use DatabaseManagement\Core\Entity\Property\Flags\Unique;
use DatabaseManagement\Core\Entity\Property\Property;
use DatabaseManagement\Core\Entity\Property\Relation\ManyToManyProperty;
use DatabaseManagement\Core\Entity\Property\Relation\OneToManyProperty;
use DatabaseManagement\Core\Entity\Property\Relation\RelationProperty;

class TableSchemaBuilder extends SchemaBuilder
{

    private static array $tableSchemaBuilders = [];

    private function __construct(private EntityDefinition $definition)
    {
        parent::__construct($this->definition);
    }

    public function create() : array
    {
        $queries = [];

        $uniqueIndexes = $this->getUniqueIndexesString($this->getUniqueIndex());
        $indexes = $this->getIndexesString($this->getIndexes());
        $primaryKeys = $this->getPrimaryKeysString($this->getPrimaryKeys());

        $properties = rtrim($this->getTableVars() . ', ' . $uniqueIndexes . ', ' . $indexes . ', ' . $primaryKeys, ', ');

        $queries[] = 'CREATE TABLE IF NOT EXISTS `' . $this->getTableName() . '` (' . $properties . ') DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;';

        $manyToManyProperties = $this->getManyToManyProperties();

        if($this->hasManyToManyRelation()) {
            $manyToManyTables = $this->createManyToManyTables($manyToManyProperties);

            $queries = array_merge($queries, $manyToManyTables);
        }

        $foreignKeys = $this->getForeignKeys();
        $queries = array_merge($queries, [$foreignKeys]);

        return $queries;
    }

    public function update()
    {

    }

    public function delete() : array
    {
        $queries = [];

        foreach ($this->getTables() as $table => $properties) {
            $queries[] = 'TRUNCATE `' . $table . '`';
        }

        return $queries;
    }

    protected function getTableVars() : string
    {
        $tableVars = '';

        foreach ($this->getAllProperties() as $property) {
            if(!($property instanceof Property)) continue;
            if($property instanceof OneToManyProperty) continue;
            if($property instanceof ManyToManyProperty) continue;

            $propertyExtension = '';
            if($property instanceof RelationProperty) {
                $propertyExtension = '_id';
            }

            // {Property} {Type}({Length}) {OPTIONALS}
            $length = '';
            if($property->getLength() != -1) {
                $length = '(' . $property->getLength() . ')';
            }

            $optionals = '';
            foreach ($property->getFlags() as $flag) {
                switch (get_class($flag)) {
                    case Nullable::class:
                        $optionals = ' NULL DEFAULT NULL';
                        break;
                    case Required::class:
                        $optionals = ' NOT NULL';
                        break;
                    case AutoIncrement::class:
                        $optionals = ' AUTO_INCREMENT';
                        break;
                }
            }

            $tableVars .= $property->getProperty() . $propertyExtension . ' ' . $property->getType() . $length . $optionals . ', ';
        }

        $tableVars = rtrim($tableVars, ', ');

        return $tableVars;
    }

    protected function createManyToManyTables(array $manyToManyProperties) : array
    {
        $manyToManyTables = [];

        foreach ($manyToManyProperties as $property) {
            $firstEntity = $this->definition->getEntityName();
            $secondEntity = $property->getReferencedEntity();

            $tableName = $firstEntity . '_' . $secondEntity;

            $properties = $firstEntity . '_id ' . $property->getType() . '(' . $property->getLength() . ') NOT NULL, ' . $secondEntity . '_id ' . $property->getType() . '(' . $property->getLength() . ') NOT NULL,';

            $firstEntityHash = $this->hash($tableName) . $this->hash($firstEntity);
            $secondEntityHash = $this->hash($tableName) . $this->hash($secondEntity);

            $indexes = 'INDEX IDX_' . $firstEntityHash . ' (' . $firstEntity . '_id), INDEX IDX_' . $secondEntityHash . ' (' . $secondEntity . '_id),';

            $primaryKeys = 'PRIMARY KEY(' . $firstEntity . '_id, ' . $secondEntity . '_id)';

            $table = 'CREATE TABLE IF NOT EXISTS `' . $tableName . '` (' . $properties . ' ' . $indexes . ' ' . $primaryKeys . ') DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;';

            $manyToManyTables[] = $table;
        }

        return $manyToManyTables;
    }

    protected function getPrimaryKeys() : array
    {
        $primaryKeys = [];

        foreach ($this->getAllProperties() as $property) {
            if(!($property instanceof Property)) continue;
            if($property instanceof OneToManyProperty) continue;
            if($property instanceof ManyToManyProperty) continue;

            $propertyExtension = '';
            if($property instanceof RelationProperty) {
                $propertyExtension = '_id';
            }

            foreach ($property->getFlags() as $flag) {
                if(!($flag instanceof PrimaryKey)) continue;

                $primaryKeys[] = $property->getProperty() . $propertyExtension;
                break;
            }
        }

        return $primaryKeys;
    }

    protected function getPrimaryKeysString(array $primaryKeys) : string|null
    {
        if(count($primaryKeys) == 0) return null;

        $primaryKeyString = 'PRIMARY KEY (';

        foreach ($primaryKeys as $primaryKey) {
            $primaryKeyString .= $primaryKey.', ';
        }

        $primaryKeyString = rtrim($primaryKeyString, ', ');

        $primaryKeyString .= ')';

        return $primaryKeyString;
    }

    protected function getUniqueIndex() : array
    {
        $uniqueIndexes = [];

        foreach ($this->getAllProperties() as $property) {
            if(!($property instanceof Property)) continue;
            if($property instanceof OneToManyProperty) continue;
            if($property instanceof ManyToManyProperty) continue;

            $propertyExtension = '';
            if($property instanceof RelationProperty) {
                $propertyExtension = '_id';
            }

            foreach ($property->getFlags() as $flag) {
                if(!($flag instanceof Unique)) continue;

                $uniqueIndexes[] = $property->getProperty() . $propertyExtension;
                break;
            }
        }

        return $uniqueIndexes;
    }

    protected function getUniqueIndexesString(array $uniqueIndexesProperties) : string|null
    {
        if(count($uniqueIndexesProperties) == 0) return null;

        $uniqueIndexes = '';

        foreach ($uniqueIndexesProperties as $uniqueIndex) {
            $tableHash = $this->hash($this->definition->getEntityName());
            $propertyHash = $this->hash($uniqueIndex);

            $uniqueIndexes .= 'UNIQUE INDEX UNIQ_' . $tableHash . $propertyHash . ' (' . $uniqueIndex . '), ';
        }

        $uniqueIndexes = rtrim($uniqueIndexes, ', ');

        return $uniqueIndexes;
    }

    protected function getIndexes() : array
    {
        $relationIndexes = [];

        foreach ($this->getAllProperties() as $property) {
            if(!($property instanceof Property)) continue;
            if($property instanceof OneToManyProperty) continue;
            if($property instanceof ManyToManyProperty) continue;

            $propertyExtension = '';
            if($property instanceof RelationProperty) {
                $propertyExtension = '_id';
            }

            $relationIndexes[] = $property->getProperty() . $propertyExtension;
        }

        return $relationIndexes;
    }

    protected function getIndexesString(array $properties) : string|null
    {
        if(count($properties) == 0) return null;

        $indexes = '';

        foreach ($properties as $property) {
            $tableHash = $this->hash($this->definition->getEntityName());
            $propertyHash = $this->hash($property);

            $indexes .= 'INDEX IDX_' . $tableHash . $propertyHash . ' (' . $property . '), ';
        }

        $indexes = rtrim($indexes, ', ');

        return $indexes;
    }

    protected function getForeignKeys() : array|null
    {
        $foreignKeys = [];

        foreach ($this->getAllProperties() as $property) {
            if (!($property instanceof RelationProperty)) continue;
            if($property instanceof OneToManyProperty) continue;
            if ($property instanceof ManyToManyProperty) {
                $firstEntity = $this->definition->getEntityName();
                $secondEntity = $property->getReferencedEntity();

                $firstReferencedProperty = 'id';
                $secondReferencedProperty = 'id';

                $firstProperty = $firstEntity . '_' . $firstReferencedProperty;
                $secondProperty = $secondEntity . '_' . $secondReferencedProperty;

                $tableName = $firstEntity . '_' . $secondEntity;

                $firstPropertyHash = $this->hash($tableName) . $this->hash($firstProperty);
                $secondPropertyHash = $this->hash($tableName) . $this->hash($secondProperty);

                $foreignKeys[] = 'ALTER TABLE `' . $tableName . '` ADD CONSTRAINT FK_' . $firstPropertyHash . ' FOREIGN KEY (' . $firstProperty . ') REFERENCES ' . $firstEntity . ' (' . $firstReferencedProperty . ') ON DELETE CASCADE;';
                $foreignKeys[] = 'ALTER TABLE `' . $tableName . '` ADD CONSTRAINT FK_' . $secondPropertyHash . ' FOREIGN KEY (' . $secondProperty . ') REFERENCES ' . $secondEntity . ' (' . $secondReferencedProperty . ') ON DELETE CASCADE;';

                continue;
            }

            $propertyExtension = '_id';

            $tableNameHash = $this->hash($this->definition->getEntityName());
            $propertyHash = $this->hash($property->getProperty());

            $foreignKeys[] = 'ALTER TABLE `' . $this->definition->getEntityName() . '` ADD CONSTRAINT FK_' . $tableNameHash . $propertyHash . ' FOREIGN KEY (' . $property->getProperty() . $propertyExtension . ') REFERENCES ' . $property->getReferencedEntity() . ' (' . $property->getReferencedProperty() . ') ON DELETE CASCADE;';
        }

        return $foreignKeys;
    }

    public static function getDefaultData() : array
    {
        return [
            'INSERT INTO `role` (id, name, created_at) VALUES ("1", "Administrator", "' . self::getDate() . '");',
            'INSERT INTO `role` (id, name, created_at) VALUES ("2", "VIP", "' . self::getDate() . '");',
            'INSERT INTO `role` (id, name, created_at) VALUES ("3", "Sponsor", "' . self::getDate() . '");',
            'INSERT INTO `role` (id, name, created_at) VALUES ("4", "Nutzer", "' . self::getDate() . '");',
            'INSERT INTO `account` (id, name, primary_role_id, created_at) VALUES ("1", "Administrator", "1", "' . self::getDate() . '");',
            'INSERT INTO `account` (id, name, primary_role_id, created_at) VALUES ("2", "NewDavis", "1", "' . self::getDate() . '");',
            'INSERT INTO `account` (id, name, primary_role_id, created_at) VALUES ("3", "Demo", "2", "' . self::getDate() . '");',
            'INSERT INTO `upload` (id, filename, file_extension, uploader_id, created_at) VALUES ("1", "icon.png", ".png", "1", "' . self::getDate() . '");',
            'INSERT INTO `upload` (id, filename, file_extension, uploader_id, created_at) VALUES ("2", "arbeit.pdf", ".pdf", "1", "' . self::getDate() . '");',
            'INSERT INTO `upload` (id, filename, file_extension, uploader_id, created_at) VALUES ("3", "hack.exe", ".docx", "1", "' . self::getDate() . '");',
            'INSERT INTO `upload` (id, filename, file_extension, uploader_id, created_at) VALUES ("4", "icon4.png", ".xls", "1", "' . self::getDate() . '");',
            'INSERT INTO `account_role` (account_id, role_id) VALUES ("1", "1");',
            'INSERT INTO `account_role` (account_id, role_id) VALUES ("2", "1");',
            'INSERT INTO `account_role` (account_id, role_id) VALUES ("3", "2");',
            'INSERT INTO `account_role` (account_id, role_id) VALUES ("3", "3");',
            'INSERT INTO `account_role` (account_id, role_id) VALUES ("3", "4");'
        ];
    }

    /**
     * @return array
     */
    public static function getEntitySchemaBuilder(EntityDefinition $definition): TableSchemaBuilder
    {
        $key = $definition->getEntityName();

        if(!array_key_exists($key, self::$tableSchemaBuilders)) {
            $entitySchemaBuilder = new self($definition);
            self::$tableSchemaBuilders[$key] = $entitySchemaBuilder;
        }

        return self::$tableSchemaBuilders[$key];
    }

}