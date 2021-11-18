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


namespace LaborDigital\T3ba\ExtConfigHandler\Table;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigHandler;
use LaborDigital\T3ba\ExtConfig\Traits\DelayedConfigExecutionTrait;
use LaborDigital\T3ba\ExtConfigHandler\Core\Handler;
use LaborDigital\T3ba\T3baFeatureToggles;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Neunerlei\Inflection\Inflector;

class TcaTableHandler extends AbstractExtConfigHandler implements NoDiInterface
{
    use DelayedConfigExecutionTrait;
    
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
        $configurator->executeThisHandlerAfter(Handler::class);
    }
    
    /**
     * @inheritDoc
     */
    public function prepare(): void { }
    
    /**
     * @inheritDoc
     */
    public function handle(string $class): void
    {
        $tableName = $this->getTableNameForClassName($class);
        
        NamingUtil::$tcaTableClassNameMap[$class] = $tableName;
        $this->storedTableNameMap[$class] = $tableName;
        
        $listKey = $this->definition->isOverride($class) ? 'override' : 'default';
        
        $this->saveDelayedConfig($this->context, 'tca.loadableTables.' . $listKey, $class, $tableName);
    }
    
    /**
     * @inheritDoc
     */
    public function finish(): void
    {
        $this->context->getState()->mergeIntoArray('tca.classNameMap', $this->storedTableNameMap);
        
        // @todo remove this in the next major release
        $this->context->getState()->mergeIntoArray('tca.meta.classNameMap', $this->storedTableNameMap);
    }
    
    /**
     * Receives the absolute class name and either uses getTableName() if the class implements,
     * TcaTableNameProviderInterface or inflects the name of the table based on the convention.
     *
     * @param   string  $class
     *
     * @return string
     */
    protected function getTableNameForClassName(string $class): string
    {
        // Shortcut if the class has a specific name provided
        if (in_array(TcaTableNameProviderInterface::class, class_implements($class), true)) {
            return call_user_func([$class, 'getTableName']);
        }
        
        $namespaceParts = explode('\\', $class);
        
        // Remove optional vendor and extKey
        $part = array_shift($namespaceParts);
        if ($part === $this->context->getVendor()) {
            array_shift($namespaceParts);
        }
        
        // Remove Configuration and Table parts
        $namespaceParts = array_filter($namespaceParts, static function (string $part) {
            return ! in_array($part, ['Configuration', 'Table'], true);
        });
        
        if ($this->context->getTypoContext()->config()
                          ->isFeatureEnabled(T3baFeatureToggles::TCA_V11_NESTED_TABLE_NAMES)) {
            $lastPart = array_pop($namespaceParts);
            $secondToLastPart = array_pop($namespaceParts);
            
            if (! empty($secondToLastPart) && str_starts_with($lastPart, $secondToLastPart)) {
                $_lastPart = substr($lastPart, strlen($secondToLastPart));
                // ONLY override the last part if we removed an exact match and the table is still camel-case
                // AND the table is not a "mm" table
                if (ucfirst($_lastPart) === $_lastPart && ! str_starts_with(strtolower($_lastPart), 'mm')) {
                    $lastPart = $_lastPart;
                }
                unset($_lastPart);
            }
            
            $namespaceParts[] = $secondToLastPart;
            if (! empty($lastPart)) {
                $namespaceParts[] = $lastPart;
            }
        }
        
        $tableNamespace = '\\' . implode('\\', $namespaceParts);
        $tableNamespace = preg_replace(
            ['~\\\\Overrides?\\\\~', '~(?:Overrides?)?(?:Tables?)?(?:Overrides?)?$~'],
            ['\\', ''],
            $tableNamespace
        );
        
        // Compile table name
        return $this->context->resolveTableName(
            '...' . implode('_',
                array_map(
                    static function (string $part) {
                        return strtolower(Inflector::toCamelBack($part));
                    },
                    array_filter(
                        explode('\\', $tableNamespace)
                    )
                )
            )
        );
    }
}
