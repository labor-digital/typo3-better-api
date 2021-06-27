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


namespace LaborDigital\T3ba\Tool\Cache\Implementation;


use LaborDigital\T3ba\Tool\TypoContext\TypoContext;

/**
 * Class FrontendCache
 *
 * Registered into the "pages" cache group, the frontend cache will be cleared with the "green flash"
 * in the backend. It will also "listen" for updates, when either the TSFE is set to no_cache,
 * or the http-pragma header is set to "no-cache" and a backend user is logged in.
 * Additionally it is automatically aware of the "environment"
 *
 * @package LaborDigital\T3ba\Tool\Cache\Implementation
 */
class FrontendCache extends AbstractExtendedCache
{
    /**
     * Internal state storage that is either true if the cache is currently in update mode, false if not,
     * or null if the state was not yet resolved.
     *
     * @var bool|null
     */
    protected $isUpdateState;
    
    /**
     * @inheritDoc
     */
    protected function isUpdate(): bool
    {
        if ($this->isUpdateState === null) {
            $typoContext = TypoContext::getInstance();
            
            $isUpdate = false;
            $tsfe = $typoContext->di()->cs()->tsfe;
            if ($tsfe->hasTsfe() && $tsfe->getTsfe()->no_cache) {
                $isUpdate = true;
            }
            
            if (! $isUpdate && $_SERVER['HTTP_PRAGMA'] === 'no-cache' && $typoContext->beUser()->isLoggedIn()) {
                $isUpdate = true;
            }
            
            $this->isUpdateState = $isUpdate;
        }
        
        return $this->isUpdateState;
    }
    
}
