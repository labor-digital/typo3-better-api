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


namespace LaborDigital\T3ba\Event\Cache;

use LaborDigital\T3ba\Tool\TypoContext\TypoContext;

/**
 * Class EnvironmentCacheKeyArgFilterEvent
 *
 * Receives the prepared list of environment cache key arguments that you can enhance for your project's requirements.
 * Note: This is executed everytime a cache key is generated!
 *
 * @package LaborDigital\T3ba\Event\Cache
 */
class EnvironmentCacheKeyArgFilterEvent
{
    /**
     * The prepared cache key arguments
     *
     * @var array
     */
    protected $args;
    
    /**
     * @var \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     */
    protected $context;
    
    /**
     * EnvironmentCacheKeyFilterEvent constructor.
     *
     * @param   array                                            $args
     * @param   \LaborDigital\T3ba\Tool\TypoContext\TypoContext  $context
     */
    public function __construct(array $args, TypoContext $context)
    {
        $this->args = $args;
        $this->context = $context;
    }
    
    /**
     * Returns the prepared cache key arguments
     *
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }
    
    /**
     * Updates the prepared cache key arguments
     *
     * @param   array  $args
     *
     * @return $this
     */
    public function setArgs(array $args): self
    {
        $this->args = $args;
        
        return $this;
    }
    
    /**
     * Returns the TypoContext object
     *
     * @return \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     */
    public function getContext(): TypoContext
    {
        return $this->context;
    }
}
