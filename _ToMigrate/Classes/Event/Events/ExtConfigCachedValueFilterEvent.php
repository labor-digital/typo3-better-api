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
 * Last modified: 2020.03.19 at 11:59
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption;

/**
 * Class ExtConfigCachedValueFilterEvent
 *
 * Emitted when an ext config object generated a cached value for some sort of configuration.
 * This is done before the value will be put into the cache!
 *
 * @package LaborDigital\Typo3BetterApi\ExtConfig\Event
 */
class ExtConfigCachedValueFilterEvent
{
    
    /**
     * The result of the executed generator
     *
     * @var mixed
     */
    protected $result;
    
    /**
     * The cache key to store the result with
     *
     * @var string|null
     */
    protected $cacheKey;
    
    /**
     * The unique key of the cached value
     *
     * @var string
     */
    protected $key;
    
    /**
     * The generator callback that was executed
     *
     * @var callable
     */
    protected $generator;
    
    /**
     * Additional data that may have been passed to the generator
     *
     * @var array
     */
    protected $additionalData;
    
    /**
     * The context object that was used to generate the result
     *
     * @var \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
     */
    protected $context;
    
    /**
     * The option the cached value is been generated for
     *
     * @var \LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption
     */
    protected $option;
    
    /**
     * The current list of elements that are registered for the cached value generator of a certain $key
     *
     * @var array
     */
    protected $config;
    
    /**
     * True if the generated value should be cached, false if not
     *
     * @var bool
     */
    protected $useCache;
    
    /**
     * ExtConfigCachedValueFilterEvent constructor.
     *
     * @param   mixed                                                                  $result
     * @param   string|null                                                            $cacheKey
     * @param   string                                                                 $key
     * @param   callable                                                               $generator
     * @param   array                                                                  $additionalData
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext                $context
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption  $option
     * @param   array                                                                  $config
     * @param   bool                                                                   $useCache
     */
    public function __construct(
        $result,
        ?string $cacheKey,
        string $key,
        callable $generator,
        array $additionalData,
        ExtConfigContext $context,
        AbstractExtConfigOption $option,
        array $config,
        bool $useCache
    ) {
        $this->result         = $result;
        $this->cacheKey       = $cacheKey;
        $this->key            = $key;
        $this->generator      = $generator;
        $this->additionalData = $additionalData;
        $this->context        = $context;
        $this->option         = $option;
        $this->config         = $config;
        $this->useCache       = $useCache;
    }
    
    /**
     * Returns the result of the executed generator
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }
    
    /**
     * Updates the result of the executed generator
     *
     * @param   mixed  $result
     *
     * @return ExtConfigCachedValueFilterEvent
     */
    public function setResult($result)
    {
        $this->result = $result;
        
        return $this;
    }
    
    /**
     * Returns true if the generated value should be cached, false if not
     *
     * @return bool
     */
    public function isUseCache(): bool
    {
        return $this->useCache;
    }
    
    /**
     * Updates the state if the generated value should be cached or not
     *
     * @param   bool  $useCache
     *
     * @return ExtConfigCachedValueFilterEvent
     */
    public function setUseCache(bool $useCache): ExtConfigCachedValueFilterEvent
    {
        $this->useCache = $useCache;
        
        return $this;
    }
    
    /**
     * Returns the cache key to store the result with
     *
     * @return string|null
     */
    public function getCacheKey(): ?string
    {
        return $this->cacheKey;
    }
    
    /**
     * Returns the unique key of the cached value
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }
    
    /**
     * Returns the generator callback that was executed
     *
     * @return callable
     */
    public function getGenerator(): callable
    {
        return $this->generator;
    }
    
    /**
     * Returns additional data that may have been passed to the generator
     *
     * @return array
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
    
    /**
     * Returns the context object that was used to generate the result
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
     */
    public function getContext(): ExtConfigContext
    {
        return $this->context;
    }
    
    /**
     * Returns the option the cached value is been generated for
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption
     */
    public function getOption(): AbstractExtConfigOption
    {
        return $this->option;
    }
    
    /**
     * Returns the current list of elements that are registered for the cached value generator of a certain $key
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
