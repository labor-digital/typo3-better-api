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


use LaborDigital\T3BA\ExtConfig\Abstracts\AbstractExtConfigHandler;
use LaborDigital\T3BA\ExtConfig\ExtConfigException;
use LaborDigital\T3BA\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Neunerlei\Inflection\Inflector;

class Handler extends AbstractExtConfigHandler
{
    /**
     * The collected table definitions for the table loader
     *
     * @var array
     */
    protected $loadableTables = [];

    /**
     * The list of generated table class -> table name mappings, that should be injected into
     * NamingUtil::$tcaTableClassNameMap at runtime.
     *
     * @var array
     */
    protected $storedTableNameMap = [];

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
    public function prepare(): void
    {
        logFile($this->definition->getConfigClasses());
    }

    /**
     * @inheritDoc
     */
    public function handle(string $class): void
    {
        $tableName = $this->getTableNameForClassName($class);

        NamingUtil::$tcaTableClassNameMap[$class] = $tableName;
        $this->storedTableNameMap[$class]         = $tableName;

        $listKey = $this->definition->isOverride($class) ? 'override' : 'default';

        $this->loadableTables[$listKey][$tableName][] = [
            'className' => $class,
            'namespace' => $this->context->getNamespace(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function finish(): void
    {
        $this->context->getState()
                      ->setAsJson('tca.loadableTables', $this->loadableTables)
                      ->mergeIntoArray('tca.meta.classNameMap', $this->storedTableNameMap);
    }


    /**
     * Receives the absolute class name and either uses getTableName() if the class implements,
     * TcaTableNameProviderInterface or inflects the name of the table based on the convention.
     *
     * @param   string  $class
     *
     * @return string
     * @throws \LaborDigital\T3BA\ExtConfig\ExtConfigException
     */
    protected function getTableNameForClassName(string $class): string
    {
        // Shortcut if the class has a specific name provided
        if (in_array(TcaTableNameProviderInterface::class, class_implements($class), true)) {
            return call_user_func([$class, 'getTableName']);
        }

        // Remove the unwanted prefixes from the namespace
        $extName        = NamingUtil::extensionNameFromExtKey($this->context->getExtKey());
        $pattern        = '(?:\\\\)?' . preg_quote($this->context->getVendor(), '~') . '\\\\' .
                          preg_quote($extName, '~') . '\\\\' .
                          'Configuration\\\\Table\\\\' .
                          '(?:Overrides?\\\\)?';
        $pattern        = '~' . $pattern . '~';
        $tableNamespace = preg_replace($pattern, '', $class);

        // Check if the transformation was successful
        if ($tableNamespace === $class) {
            throw new ExtConfigException(
                'The TCA table config class ' . $class . ' MUST start with the following PHP namespace: '
                . $this->context->getVendor() . '\\' . $extName . '\\'
                . 'Configuration\\Table\\...');
        }

        // Remove optional "table" suffix
        if (substr($tableNamespace, -5) === 'Table') {
            $tableNamespace = substr($tableNamespace, 0, -5);
        }

        // Compile table name
        return $this->context->resolveTableName(
            '...' . implode('_',
                array_map(
                    static function (string $part) {
                        return strtolower(Inflector::toCamelBack($part));
                    },
                    explode('\\', $tableNamespace)
                )
            )
        );
    }
}
