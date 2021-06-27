<?php
/*
 * Copyright 2021 LABOR.digital
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Sql;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Core\Exception\NotImplementedException;

class TableOverride extends Table implements NoDiInterface
{
    protected $locked = true;
    
    /**
     * Unlocks the safety exceptions on some methods
     */
    public function unlock(): void
    {
        $this->locked = false;
    }
    
    /**
     * @inheritDoc
     */
    public function setPrimaryKey(array $columnNames, $indexName = false)
    {
        $this->ensureColumnsExist($columnNames);
        
        return parent::setPrimaryKey($columnNames, $indexName);
    }
    
    /**
     * @inheritDoc
     */
    public function addIndex(array $columnNames, $indexName = null, array $flags = [], array $options = [])
    {
        $this->ensureColumnsExist($columnNames);
        
        try {
            return parent::addIndex($columnNames, $indexName, $flags, $options);
        } catch (SchemaException $e) {
            // Ignore if index exists
        }
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function addUniqueIndex(array $columnNames, $indexName = null, array $options = [])
    {
        $this->ensureColumnsExist($columnNames);
        
        try {
            return parent::addUniqueIndex($columnNames, $indexName, $options);
        } catch (SchemaException $e) {
            // Ignore if index exists
        }
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function addForeignKeyConstraint(
        $foreignTable,
        array $localColumnNames,
        array $foreignColumnNames,
        array $options = [],
        $constraintName = null
    )
    {
        $this->ensureColumnsExist($localColumnNames);
        
        try {
            return parent::addForeignKeyConstraint(
                $foreignTable, $localColumnNames, $foreignColumnNames, $options, $constraintName);
        } catch (SchemaException $e) {
            // Ignore if constraint exists
        }
        
        return $this;
    }
    
    /**
     * @inheritDoc
     * @throws \LaborDigital\T3ba\Core\Exception\NotImplementedException
     */
    public function getPrimaryKeyColumns()
    {
        throw new NotImplementedException('This method is unreliable here! Please don\'t use it!');
    }
    
    /**
     * @inheritDoc
     * @throws \LaborDigital\T3ba\Core\Exception\NotImplementedException
     */
    public function dropColumn($name)
    {
        if ($this->locked) {
            throw new NotImplementedException('This method is unreliable here! Please don\'t use it!');
        }
        
        return parent::dropColumn($name);
    }
    
    /**
     * @inheritDoc
     * @throws \LaborDigital\T3ba\Core\Exception\NotImplementedException
     */
    public function getColumn($name, bool $useAnyway = false): Column
    {
        if ($this->locked && ! $useAnyway) {
            throw new NotImplementedException('This method is unreliable here! The column might be reconfigured in the TCA builder. I recommend not using this method here! If you know what you are doing, set the second parameter to TRUE, to access it anyway!');
        }
        
        return parent::getColumn($name);
    }
    
    /**
     * @inheritDoc
     * @throws \LaborDigital\T3ba\Core\Exception\NotImplementedException
     */
    public function hasColumn($name, bool $useAnyway = false): bool
    {
        if (parent::hasColumn($name)) {
            return true;
        }
        
        if ($this->locked && ! $useAnyway) {
            throw new NotImplementedException('This method is unreliable here! The column might be defined in the TCA builder, so it COULD be true even if this method returns false. I recommend not using this method here! If you know what you are doing, set the second parameter to TRUE, to access it anyway!');
        }
        
        return false;
    }
    
    /**
     * @inheritDoc
     * @throws \LaborDigital\T3ba\Core\Exception\NotImplementedException
     */
    public function getColumns(bool $useAnyway = false): array
    {
        if ($this->locked && ! $useAnyway) {
            throw new NotImplementedException('This method is unreliable here! The column definition is based on the TCA builder, so this will NEVER return the correct value! Pass TRUE to the method to use it anyway.');
        }
        
        return parent::getColumns();
    }
    
    /**
     * Internal workaround to make sure the logic does not crash even if a field is not defined
     * -> meaning it will probably be created in the TCA object
     *
     * @param   array  $columnNames
     */
    protected function ensureColumnsExist(array $columnNames): void
    {
        foreach ($columnNames as $columnName) {
            if (! $this->hasColumn($columnName, true)) {
                $this->addColumn($columnName, SqlRegistry::FALLBACK_TYPE_NAME);
            }
        }
    }
}
