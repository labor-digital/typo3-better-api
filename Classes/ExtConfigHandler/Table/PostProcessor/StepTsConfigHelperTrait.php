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
 * Last modified: 2021.11.16 at 23:14
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor;


use Neunerlei\Configuration\State\ConfigState;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

trait StepTsConfigHelperTrait
{
    /**
     * Contains the ts config scripts that are collected by the post processor
     *
     * @var array
     */
    protected $ts = [];
    
    /**
     * Sets the given typoScript code into the pageTsConfig storage
     *
     * @param   ConfigState  $configState  The config state to inject the ts config into
     * @param   string       $importPath   The storage path where to store the configuration
     */
    protected function addTsConfigToState(ConfigState $configState, string $importPath): void
    {
        $ts = implode(PHP_EOL, array_filter(array_map('trim', $this->ts)));
        $ts = '[GLOBAL]' . PHP_EOL . $ts;
        $configState->set('typo.typoScript.dynamicTypoScript.' . str_replace('.', '\\.', $importPath), $ts);
        
        // For THIS request, the pageTsConfig has already been loaded.
        // Therefore we need to inject it into the core directly
        ExtensionManagementUtility::addPageTSConfig($ts);
        
        $pageTsConfig = $configState->get('typo.typoScript.pageTsConfig', '');
        $importStatement = '@import \'dynamic:' . $importPath . '\'';
        if (! str_contains($pageTsConfig, $importStatement)) {
            $configState->attachToString('typo.typoScript.pageTsConfig', '[GLOBAL]' . PHP_EOL . $importStatement, true);
        }
    }
}