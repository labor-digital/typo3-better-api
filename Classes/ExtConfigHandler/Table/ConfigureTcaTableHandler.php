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
 * Last modified: 2021.01.13 at 19:31
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Table;


use LaborDigital\T3BA\ExtConfig\AbstractGroupExtConfigHandler;
use LaborDigital\T3BA\ExtConfig\ExtConfigException;
use LaborDigital\T3BA\ExtConfig\StandAloneHandlerInterface;
use LaborDigital\T3BA\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableDumper;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableFactory;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Neunerlei\Inflection\Inflector;

class ConfigureTcaTableHandler extends AbstractGroupExtConfigHandler implements StandAloneHandlerInterface
{

    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableFactory
     */
    protected $tableFactory;

    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableDumper
     */
    protected $tableDumper;

    /**
     * A list of tca configuration class names and their matching db table names
     *
     * @var array
     */
    protected $classNameTableMap = [];

    /**
     * The object representation of the currently configured table
     *
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable|null
     */
    protected $table;

    /**
     * If true the finder will load table overrides instead of the normal table configurations
     *
     * @var bool
     */
    protected $loadOverrides = false;

    public function __construct(TableFactory $tableFactory, TableDumper $tableDumper)
    {
        $this->tableFactory = $tableFactory;
        $this->tableDumper  = $tableDumper;
    }

    /**
     * Allows you to toggle if the finder will load table overrides instead of the normal table configurations
     *
     * @param   bool  $state
     *
     * @return $this
     */
    public function setLoadOverrides(bool $state): self
    {
        $this->loadOverrides = $state;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $configurator->registerLocation('Configuration/Table');
        $configurator->registerOverrideLocation('Override');
        // We register "Override>s<" for the TYPO3 natives that could otherwise feel a bit lost ;)
        $configurator->registerOverrideLocation('Overrides');
        $configurator->registerInterface(ConfigureTcaTableInterface::class);
    }

    /**
     * @inheritDoc
     */
    protected function getGroupKeyOfClass(string $class): string
    {
        // Filter the tables based on our override status
        if ($this->definition->isOverride($class) !== $this->loadOverrides) {
            return '';
        }

        // Shortcut if the class has a specific name provided
        if (in_array(TcaTableNameProviderInterface::class, class_implements($class), true)) {
            return $this->classNameTableMap[$class] = call_user_func([$class, 'getTableName']);
        }

        // Remove the unwanted prefix from the namespace
        $pattern        = '(?:\\\\)?' . preg_quote($this->context->getVendor(), '~') . '\\\\' .
                          preg_quote(Inflector::toCamelCase($this->context->getExtKey()), '~') . '\\\\' .
                          'Configuration\\\\Table\\\\' .
                          '(?:Overrides?\\\\)?';
        $pattern        = '~' . $pattern . '~';
        $tableNamespace = preg_replace($pattern, '', $class);

        // Check if the transformation was successful
        if ($tableNamespace === $class) {
            throw new ExtConfigException(
                'The TCA table config class ' . $class . ' MUST start with the following PHP namespace: '
                . $this->context->getVendor() . '\\' . Inflector::toCamelCase($this->context->getExtKey()) . '\\'
                . 'Configuration\\Table\\...');
        }

        // Compile table name
        $tableName = implode('_', array_filter(
            array_merge([
                'tx',
                NamingUtil::flattenExtKey($this->context->getExtKey()),
                'domain',
                'model',
            ],
                array_map(static function (string $part) {
                    return strtolower(Inflector::toCamelBack($part));
                }, explode('\\', $tableNamespace)))
        ));

        return $this->classNameTableMap[$class] = $tableName;
    }

    /**
     * @inheritDoc
     */
    public function prepareHandler(): void
    {
        // Link the class name table map to the naming utility so we can use it as we learn about the tables
        $this->classNameTableMap = &NamingUtil::$tcaTableClassNameMap;
    }

    /**
     * @inheritDoc
     */
    public function prepareGroup(string $groupKey, array $groupClasses): void
    {
        // Ignore disabled
        if ($groupKey === '') {
            $this->table = null;

            return;
        }

        $this->table = $this->tableFactory->create($groupKey, $this->context);
        $this->tableFactory->initialize($this->table);
    }

    /**
     * @inheritDoc
     */
    public function handleGroupItem(string $class): void
    {
        if ($this->table) {
            call_user_func([$class, 'configureTable'], $this->table, $this->context);
        }
    }

    /**
     * @inheritDoc
     */
    public function finishGroup(string $groupKey, array $groupClasses): void
    {
        if ($this->table) {
            $GLOBALS['TCA'][$groupKey] = $this->tableDumper->dump($this->table);
        }
    }

    /**
     * @inheritDoc
     */
    public function finishHandler(): void { }
}
