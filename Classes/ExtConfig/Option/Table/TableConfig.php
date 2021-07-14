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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\Table;

class TableConfig
{

    /**
     * The list of tables that are allowed on standard pages
     *
     * @var array
     */
    public $tablesOnStandardPages = [];

    /**
     * The typoscript configuration for the tables
     *
     * @var string
     */
    public $typoScript = '';

    /**
     * PagesTsConfig to inject for the tables
     *
     * @var string
     */
    public $tsConfig = '';

    /**
     * The sql definition for the tables
     *
     * @var string
     */
    public $sql = '';

    /**
     * Stores the list of the table positions when showing the list view
     *
     * @var array
     */
    public $tableListPositions = [];
}
