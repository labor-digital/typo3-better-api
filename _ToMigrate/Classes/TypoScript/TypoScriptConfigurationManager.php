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

namespace LaborDigital\Typo3BetterApi\TypoScript;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;

/**
 * Class TypoScriptConfigurationManager
 *
 * Extends the default configuration manager to make the pid publicly changeable for our API to work correctly.
 *
 * @package LaborDigital\Typo3BetterApi\Typoscript
 */
class TypoScriptConfigurationManager extends BackendConfigurationManager
{
    
    /**
     * Stores the last page id before we override it with setCurrentPid() to be able to restore it with
     * resetCurrentPid()
     *
     * @var mixed
     */
    protected $lastPageId;
    
    /**
     * Similar to the base class's setup cache, this holds the constants for parsed templates
     *
     * @var array
     */
    protected $constantCache = [];
    
    /**
     * Sets the current page id to look up the typoScript config for
     *
     * @param   int  $pid
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigurationManager
     */
    public function setCurrentPid(int $pid): TypoScriptConfigurationManager
    {
        $this->lastPageId    = $this->currentPageId;
        $this->currentPageId = $pid;
        
        return $this;
    }
    
    /**
     * Resets the last page id to the value we used before "setCurrentPid()"
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigurationManager
     */
    public function resetCurrentPid(): TypoScriptConfigurationManager
    {
        $this->currentPageId = $this->lastPageId;
        
        return $this;
    }
    
    /**
     * Returns TypoScript Setup array from current Environment.
     *
     * @return array the raw TypoScript setup
     */
    public function getTypoScriptSetup()
    {
        $currentPageId = $this->getCurrentPageId();
        
        // Simple lookup using cache / We already know the constants -> so we must have already done the heavy lifting...
        if (! empty($this->constantCache[$currentPageId])) {
            return parent::getTypoScriptSetup();
        }
        
        // Simulate singleton for template service to be able to extract the constants afterwards
        $wrapper = new class extends TemplateService implements SingletonInterface
        {
        };
        GeneralUtility::setSingletonInstance(TemplateService::class, $wrapper);
        // @todo see fi this still works
        $wrapper->backend_info = true;
        $setup                 = parent::getTypoScriptSetup();
        GeneralUtility::removeSingletonInstance(TemplateService::class, $wrapper);
        
        // Store constants
        $this->constantCache[$currentPageId] = $wrapper->setup_constants;
        
        // Done
        return $setup;
    }
    
    /**
     * Returns the TypoScript constants array from the current environment.
     *
     * @return array
     */
    public function getTypoScriptConstants(): array
    {
        $currentPageId = $this->getCurrentPageId();
        
        // Fastlane
        if (! empty($this->constantCache[$currentPageId])) {
            return $this->constantCache[$currentPageId];
        }
        
        // Load the typoScript setup
        $this->getTypoScriptSetup();
        
        // Done
        return (array)$this->constantCache[$currentPageId];
    }
}
