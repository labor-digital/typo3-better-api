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
 * Last modified: 2020.03.18 at 19:46
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\Table;


use Neunerlei\Arrays\Arrays;

trait ExtBasePersistenceMapperTrait {
	
	/**
	 * This helper receives a list of model classes and a table name they should map to.
	 * It will generate the typoscript code to configure extbase accordingly.
	 *
	 * @param array  $modelList The list of model classes to map to the table name
	 * @param string $tableName The table to map the given classes to
	 * @param array  $columns   The list of mapped columns as array of $col => $property
	 *
	 * @return string
	 */
	protected function getPersistenceTs(array $modelList, string $tableName, array $columns = []): string {
		$mapping = [];
		foreach ($modelList as $model) {
			// Map the columns of this model
			$cols = Arrays::getPath($columns, [$model], []);
			$colMap = "";
			foreach ($cols as $col => $property)
				$colMap .= "						$col.mapOnProperty = $property" . PHP_EOL;
			
			// Build the final mapping
			$mapping[] = "
			$model {
				mapping {
					tableName = $tableName
					columns {
$colMap
					}
				}
			}";
		}
		if (empty($mapping)) return "";
		$mapping = implode(PHP_EOL, $mapping);
		return "
		config.tx_extbase.persistence.classes{
			$mapping
		}";
	}
}