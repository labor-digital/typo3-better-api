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
 * Last modified: 2020.03.20 at 12:05
 */

namespace LaborDigital\Typo3BetterApi\BackendForms;


use LaborDigital\Typo3BetterApi\Event\Events\SqlTableDefinitionFilterEvent;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\EventBusInterface;
use Neunerlei\Inflection\Inflector;

class TableSqlGenerator {
	
	/**
	 * Storage for the sql definitions by table name
	 * @var array
	 */
	protected $sql = [];
	
	/**
	 * @var \Neunerlei\EventBus\EventBusInterface
	 */
	protected $eventBus;
	
	/**
	 * TableSqlGenerator constructor.
	 *
	 * @param \Neunerlei\EventBus\EventBusInterface $eventBus
	 */
	public function __construct(EventBusInterface $eventBus) {
		$this->eventBus = $eventBus;
	}
	
	/**
	 * Returns true if the given column has a definition on the given table
	 *
	 * @param string $table  The database table to check for the $column's existence
	 * @param string $column The database column to check for
	 *
	 * @return bool
	 */
	public function hasDefinitionFor(string $table, string $column): bool {
		return isset($this->sql[$table][$column]);
	}
	
	/**
	 * Can be used to add a sql definition for a certain $column at a given $table.
	 *
	 * The $definition should look like "varchar(512) DEFAULT ''  NOT NULL", or "tinyint(4)"
	 * The $definition should NOT contain the table or the column name!
	 *
	 * @param string $table      The table to set the $column's definition for
	 * @param string $column     The column to set the definition for
	 * @param string $definition The definition to set
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TableSqlGenerator
	 */
	public function setDefinitionFor(string $table, string $column, string $definition): TableSqlGenerator {
		if (empty($definition)) return $this->removeDefinitionFor($table, $column);
		$this->sql[$table][$column] = $definition;
		return $this;
	}
	
	/**
	 * Removes the definition for a given column from the registry
	 *
	 * @param string $table
	 * @param string $column
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TableSqlGenerator
	 */
	public function removeDefinitionFor(string $table, string $column): TableSqlGenerator {
		unset($this->sql[$table][$column]);
		if (empty($this->sql[$table])) unset($this->sql[$table]);
		return $this;
	}
	
	/**
	 * Returns a single definition for a column of a certain table
	 *
	 * @param string $table
	 * @param string $column
	 *
	 * @return string
	 */
	public function getDefinitionFor(string $table, string $column): string {
		return (string)Arrays::getPath($this->sql, [$table, $column], "");
	}
	
	/**
	 * Returns all registered column definitions for a given table
	 *
	 * @param string $table
	 *
	 * @return array|mixed|null
	 */
	public function getTableDefinitions(string $table) {
		$def = Arrays::getPath($this->sql, $table, []);
		unset($def["@@meta"]);
		return $def;
	}
	
	/**
	 * Removes all definitions for a single table.
	 *
	 * @param string $table
	 *
	 * @return $this
	 */
	public function removeTableDefinitions(string $table) {
		unset($this->sql[$table]);
		return $this;
	}
	
	/**
	 * Can be used to add "non-column" definitions, for example Index definitions or similar
	 *
	 * @param string $table
	 * @param string $definition
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TableSqlGenerator
	 */
	public function addTableMeta(string $table, string $definition): TableSqlGenerator {
		$this->sql[$table]["@@meta"][Inflector::toUuid($definition)] = $definition;
		return $this;
	}
	
	/**
	 * Returns the meta information for the given table
	 *
	 * @param string $table
	 *
	 * @return array
	 */
	public function getTableMeta(string $table): array {
		return array_values(Arrays::getPath($this->sql, [$table, "@@meta"], []));
	}
	
	/**
	 * Completely removes all data stored for the given table
	 *
	 * @param string $table
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TableSqlGenerator
	 */
	public function flushTableDefinition(string $table): TableSqlGenerator {
		unset($this->sql[$table]);
		return $this;
	}
	
	/**
	 * Returns the configured lines of the sql query to create this table
	 *
	 * @param string $table
	 *
	 * @return array
	 */
	public function getTableDefinition(string $table): array {
		if (isset($this->sql[$table]))
			$definition = Arrays::attach($this->getTableDefinitions($table), $this->getTableMeta($table));
		else $definition = [];
		$this->eventBus->dispatch(($e = new SqlTableDefinitionFilterEvent($table, $definition)));
		return $e->getDefinition();
	}
	
	/**
	 * Can be used to create a mm table definition in the sql file.
	 *
	 * @param string      $tableName   The table name to create the mm table for
	 * @param string      $fieldName   The field name of the given table we create the mm table for
	 * @param string|null $mmTableName Optionally the manually provided name of the mm table
	 *
	 * @return string
	 */
	public function addMmTableDefinition(string $tableName, string $fieldName, ?string $mmTableName = NULL): string {
		// Make the name of the mm table
		if (empty($mmTableName)) $mmTableName = substr(str_replace("_domain_model_", "_", $tableName) . "_" . Inflector::toUnderscore($fieldName), 0, 45) . "_mm";
		
		// Check if the table already exists
		if (isset($this->sql[$mmTableName])) return $mmTableName;
		
		// Add the definition
		$this->sql[$mmTableName] = [
			"uid"             => "int(11) NOT NULL auto_increment",
			"uid_local"       => "int(11) DEFAULT '0' NOT NULL",
			"uid_foreign"     => "int(11) DEFAULT '0' NOT NULL",
			"tablenames"      => "varchar(50) DEFAULT '' NOT NULL",
			"sorting"         => "int(11) DEFAULT '0' NOT NULL",
			"sorting_foreign" => "int(11) DEFAULT '0' NOT NULL",
			"ident"           => "varchar(30) DEFAULT '' NOT NULL",
			"@@meta"          => [
				Inflector::toUuid("KEY uid_local (uid_local)")     => "KEY uid_local (uid_local)",
				Inflector::toUuid("KEY uid_foreign (uid_foreign)") => "KEY uid_foreign (uid_foreign)",
				Inflector::toUuid("PRIMARY KEY (uid)")             => "PRIMARY KEY (uid)",
			],
		];
		
		// Done
		return $mmTableName;
	}
	
	/**
	 * Returns the full sql string for a single table
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	public function getTableSql(string $table): string {
		
		// Sanitize table name
		$tableClean = preg_replace("/[^a-zA-Z0-9-_]/", "", $table);
		
		// Build the definition list
		$statement = [];
		foreach ($this->getTableDefinition($table) as $k => $def) {
			if (!is_numeric($k)) $statement[] = "`" . preg_replace("/[^a-zA-Z0-9-_]/", "", $k) . "` " . rtrim($def, ", ");
			else $statement[] = $def;
		}
		
		// Build the statement
		return
			"--" . PHP_EOL .
			"-- Table structure for table '$tableClean'" . PHP_EOL .
			"--" . PHP_EOL .
			"CREATE TABLE `$tableClean` (" . PHP_EOL .
			"\t" . implode(", " . PHP_EOL . "\t", $statement) . PHP_EOL .
			");";
	}
	
	/**
	 * Returns the full sql string for all registered tables
	 *
	 * @return string
	 */
	public function getFullSql(): string {
		$statement = [];
		foreach (array_keys($this->sql) as $table)
			$statement[] = $this->getTableSql($table);
		return implode(PHP_EOL . PHP_EOL, $statement);
	}
	
	/**
	 * Completely resets the stored data in the builder
	 */
	public function flush() {
		$this->sql = [];
	}
	
	public function getState(): array {
		return $this->sql;
	}
	
	public function setState(array $state) {
		return $this->sql = $state;
	}
	
	/**
	 * Internal helper which is used to load the sql definition from the tca array
	 *
	 * @param string $table
	 * @param array  $definition
	 */
	public function __setByTcaDefinition(string $table, array $definition) {
		// Load for all fields
		if (is_array($definition["columns"]))
			foreach ($definition["columns"] as $k => $config)
				if (is_string($config["@sql"])) $this->setDefinitionFor($table, (string)$k, (string)$config["@sql"]);
		
		// Load table values
		if (isset($definition["@sql"])) {
			// Load columns
			if (is_array($definition["@sql"]["additionalColumns"]))
				foreach ($definition["@sql"]["additionalColumns"] as $k => $d)
					$this->setDefinitionFor($table, (string)$k, (string)$d);
			
			// Load meta
			if (is_array($definition["@sql"]["meta"]))
				foreach ($definition["@sql"]["meta"] as $d)
					$this->addTableMeta($table, (string)$d);
		}
	}
}