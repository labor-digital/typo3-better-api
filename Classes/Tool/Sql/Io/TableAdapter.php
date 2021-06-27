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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Sql\Io;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Neunerlei\Arrays\Arrays;

class TableAdapter extends Table
{
    
    /**
     * Helper to add a column instance to an existing table
     *
     * @param   \Doctrine\DBAL\Schema\Table   $table
     * @param   \Doctrine\DBAL\Schema\Column  $column
     */
    public static function attachColumn(Table $table, Column $column): void
    {
        $table->_addColumn($column);
    }
    
    /**
     * Helper to replace a column with the given column on an existing table
     *
     * @param   \Doctrine\DBAL\Schema\Table   $table
     * @param   \Doctrine\DBAL\Schema\Column  $column
     */
    public static function replaceColumn(Table $table, Column $column): void
    {
        $table->dropColumn($column->getName());
        $table->_addColumn($column);
    }
    
    /**
     * Merges the whole configuration of the $new table into the $target table object
     *
     * @param   \Doctrine\DBAL\Schema\Table  $target
     * @param   \Doctrine\DBAL\Schema\Table  $new
     */
    public static function mergeTables(Table $target, Table $new): void
    {
        foreach ($new->_columns as $key => $column) {
            if ($target->hasColumn($key)) {
                $target->dropColumn($key);
            }
            $target->_addColumn($column);
        }
        
        if ($new->hasPrimaryKey()) {
            $target->_primaryKeyName = $new->_primaryKeyName;
        }
        
        foreach ($new->_indexes as $key => $index) {
            if ($target->hasIndex($key)) {
                $target->dropIndex($key);
            }
            try {
                $target->_addIndex($index);
            } catch (SchemaException $e) {
                // Ignore issues with foreign key indexes
            }
        }
        
        foreach ($new->_fkConstraints as $constraint) {
            $target->_addForeignKeyConstraint($constraint);
        }
        
        $target->_options = Arrays::merge($target->_options, $new->getOptions());
    }
    
    /**
     * Resets the _primaryKeyName property of $table if no "primary" index exists
     *
     * @param   \Doctrine\DBAL\Schema\Table  $table
     */
    public static function dropPrimaryKeyNameIfNoIndexExists(Table $table): void
    {
        if ($table->_primaryKeyName !== false && ! isset($table->_indexes['primary'])) {
            $table->_primaryKeyName = false;
        }
    }
}
