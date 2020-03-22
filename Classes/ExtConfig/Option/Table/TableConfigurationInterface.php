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


use LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;

interface TableConfigurationInterface {
	
	/**
	 * Use this to modify the given $table object, to configure the matching tca
	 *
	 * @param TcaTable         $table      The object representing the table you want to modify
	 * @param ExtConfigContext $context    The context you are currently working in
	 * @param bool             $isOverride True if this is called as table override. False if this is called as new
	 *                                     table registration
	 *
	 * @return void
	 */
	public static function configureTable(TcaTable $table, ExtConfigContext $context, bool $isOverride): void;
	
}