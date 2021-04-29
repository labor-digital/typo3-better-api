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


namespace LaborDigital\T3BA\Tool\Cache\Implementation;


use Closure;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;

/**
 * Class PageCache
 *
 * Similar to FrontendCache but the generated key is always tied to the current PID
 *
 * @package LaborDigital\T3BA\Tool\Cache\Implementation
 */
class PageCache extends FrontendCache
{
    /**
     * @inheritDoc
     */
    public function getCacheKey($keyArgsOrGenerator, ?bool $withEnvironment = null): string
    {
        $identifier = parent::getCacheKey($keyArgsOrGenerator, $withEnvironment);
        
        return md5($identifier . '.' . $this->getPageUid());
    }
    
    /**
     * @inheritDoc
     */
    protected function wrapGeneratorCall(
        Closure $generator,
        array $options,
        array &$tags,
        ?int &$lifetime,
        bool &$enabled
    )
    {
        $tags[] = 'page_' . $this->getPageUid();
        
        return parent::wrapGeneratorCall($generator, $options, $tags, $lifetime, $enabled);
    }
    
    
    /**
     * Returns the page uid
     *
     * @return int
     */
    protected function getPageUid(): int
    {
        return TypoContext::getInstance()->pid()->getCurrent();
    }
}
