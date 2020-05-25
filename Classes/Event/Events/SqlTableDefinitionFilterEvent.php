<?php
/**
 * Copyright 2020 LABOR.digital
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
 * Last modified: 2020.03.19 at 11:25
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

/**
 * Class SqlTableDefinitionFilterEvent
 *
 * Triggered when the backend form sql table definition is generated
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class SqlTableDefinitionFilterEvent
{
    /**
     * The name of the table the definition is generated for
     * @var string
     */
    protected $tableName;
    
    /**
     * The definition that should be filtered
     * @var array
     */
    protected $definition;
    
    /**
     * SqlTableDefinitionFilterEvent constructor.
     *
     * @param string $tableName
     * @param array  $definition
     */
    public function __construct(string $tableName, array $definition)
    {
        $this->tableName = $tableName;
        $this->definition = $definition;
    }
    
    /**
     * Returns the name of the table the definition is generated for
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Returns the definition that should be filtered
     * @return array
     */
    public function getDefinition(): array
    {
        return $this->definition;
    }
    
    /**
     * Updates the definition that should be filtered
     *
     * @param array $definition
     *
     * @return SqlTableDefinitionFilterEvent
     */
    public function setDefinition(array $definition): SqlTableDefinitionFilterEvent
    {
        $this->definition = $definition;
        return $this;
    }
}
