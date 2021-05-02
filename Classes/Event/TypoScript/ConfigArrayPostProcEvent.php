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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Event\TypoScript;


use LaborDigital\T3ba\Event\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\T3ba\Event\TypoScript\Adapter\ConfigArrayPostProcEventAdapter;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class TypoScriptConfigArrayPostProcEvent
 *
 * Executed at the $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc'] hook.
 * Allows you to modify the typoScript config array after it was loaded
 *
 * @package LaborDigital\T3ba\Event
 */
class ConfigArrayPostProcEvent implements CoreHookEventInterface
{
    /**
     * The loaded configuration array to post process
     *
     * @var array
     */
    protected $config;
    
    /**
     * The typoScript frontend controller to process the data for
     *
     * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected $tsfe;
    
    /**
     * TypoScriptConfigArrayPostProcEvent constructor.
     *
     * @param   array                         $config
     * @param   TypoScriptFrontendController  $tsfe
     */
    public function __construct(array $config, TypoScriptFrontendController $tsfe)
    {
        $this->config = $config;
        $this->tsfe = $tsfe;
    }
    
    /**
     * Returns the loaded configuration array to post process
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
    
    /**
     * Updates the loaded configuration array to post process
     *
     * @param   array  $config
     *
     * @return ConfigArrayPostProcEvent
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        
        return $this;
    }
    
    /**
     * Returns the typoScript frontend controller to process the data for
     *
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    public function getTsfe(): TypoScriptFrontendController
    {
        return $this->tsfe;
    }
    
    /**
     * @inheritDoc
     */
    public static function getAdapterClass(): string
    {
        return ConfigArrayPostProcEventAdapter::class;
    }
    
}
