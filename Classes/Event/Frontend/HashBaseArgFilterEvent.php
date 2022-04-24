<?php
/*
 * Copyright 2022 LABOR.digital
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
 * Last modified: 2022.04.23 at 12:14
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Event\Frontend;


use LaborDigital\T3ba\Event\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\T3ba\Event\Frontend\Adapter\HashBaseArgFilterEventAdapter;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Allows you to filter the hashBase arguments used by the TSFE to generate the cache base value
 * It utilizes this hook: $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['createHashBase']
 */
class HashBaseArgFilterEvent implements CoreHookEventInterface
{
    protected $args;
    protected $createLockHashBase;
    
    /**
     * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected $tsfe;
    
    /**
     * @inheritDoc
     */
    public static function getAdapterClass(): string
    {
        return HashBaseArgFilterEventAdapter::class;
    }
    
    public function __construct(array $args, bool $createLockHashBase, TypoScriptFrontendController $tsfe)
    {
        $this->args = $args;
        $this->createLockHashBase = $createLockHashBase;
        $this->tsfe = $tsfe;
    }
    
    /**
     * Returns the arguments to be used when generating the cache base
     *
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }
    
    /**
     * Allows you to modify the arguments to be used when generating the cache base
     *
     * @param   array  $args
     *
     * @return HashBaseArgFilterEvent
     */
    public function setArgs(array $args): HashBaseArgFilterEvent
    {
        $this->args = $args;
        
        return $this;
    }
    
    /**
     * Defines whether to create the lock hash, which doesn't contain the "$tsfe->all" (the template information)
     *
     * @return bool
     */
    public function isCreateLockHashBase(): bool
    {
        return $this->createLockHashBase;
    }
    
    /**
     * Returns the typoscript frontend controller for the request
     *
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    public function getTsfe(): TypoScriptFrontendController
    {
        return $this->tsfe;
    }
}