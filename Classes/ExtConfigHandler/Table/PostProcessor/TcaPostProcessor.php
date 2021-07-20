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


namespace LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step\CshLabelStep;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step\DomainModelMapStep;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step\ListPositionStep;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step\PreviewLinkStep;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step\TablesOnStandardPagesStep;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;

/**
 * Class TcaPostProcessor
 *
 * Processes the TCA definition of ALL tables (even the ones that are provided in the default TYPO3 way)
 * and extracts additional configuration that should be in the TCA but is not.
 *
 * The result will be available in the configuration state under tca.meta.
 *
 * Feel free to add your own steps to create custom TCA configuration options.
 *
 * @package LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor
 */
class TcaPostProcessor implements NoDiInterface
{
    use ContainerAwareTrait;
    
    /**
     * Public API to register additional steps if you like. All steps have to be defined as class name.
     * Every class has to implement the TcaPostProcessorStepInterface
     *
     * @var string[]
     * @see TcaPostProcessorStepInterface
     */
    public static $steps
        = [
            DomainModelMapStep::class,
            ListPositionStep::class,
            TablesOnStandardPagesStep::class,
            CshLabelStep::class,
            PreviewLinkStep::class,
        ];
    
    /**
     * The list of registered processors to execute after the main steps are completed
     *
     * @var array
     */
    protected static $additionalProcessors = [];
    
    /**
     * Public api to register additional post processors that will be executed after all steps completed.
     * The list contains the $nameOfTheTable => [$callable, $anotherCallable] where the callables are executed
     * in their given order.
     *
     * @param   string    $tableName  The name of the table to apply the post processor for
     * @param   callable  $processor  The post processor callable to execute, it receives the $config,
     *                                $extractedMeta and $tableName as parameters.
     *                                It should create references to the given values in order to modify them.
     */
    public static function registerAdditionalProcessor(string $tableName, callable $processor): void
    {
        static::$additionalProcessors[$tableName][] = $processor;
    }
    
    /**
     * Executes all existing steps for the database tables
     *
     * @return array
     */
    public function process(): array
    {
        // Store the state of the class name map
        $meta['classNameMap'] = NamingUtil::$tcaTableClassNameMap;
        
        // Create the steps
        $steps = [];
        foreach (static::$steps as $stepClass) {
            $step = $this->getServiceOrInstance($stepClass);
            
            if (! $step instanceof TcaPostProcessorStepInterface) {
                continue;
            }
            
            $steps[] = $step;
        }
        
        // Iterate the tables and all steps for them
        foreach ($GLOBALS['TCA'] as $tableName => &$config) {
            foreach ($steps as $step) {
                $step->process($tableName, $config, $meta);
            }
            
            if (is_array(static::$additionalProcessors[$tableName] ?? null)) {
                foreach (static::$additionalProcessors[$tableName] as $processor) {
                    if (is_callable($processor)) {
                        $processor($config, $meta, $tableName);
                    }
                }
            }
        }
        
        return $meta;
    }
}
