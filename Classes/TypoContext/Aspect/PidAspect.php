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
 * Last modified: 2020.03.19 at 11:54
 */

namespace LaborDigital\Typo3BetterApi\TypoContext\Aspect;

use LaborDigital\Typo3BetterApi\TypoContext\Facet\PidFacet;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class PidAspect
 * @package    LaborDigital\Typo3BetterApi\TypoContext\Aspect
 *
 * @deprecated will be removed in v10 -> Use PidFacet instead
 */
class PidAspect implements AspectInterface, SingletonInterface
{
    use AutomaticAspectGetTrait;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\TypoContext\Facet\PidFacet
     */
    protected $facet;
    
    /**
     * PidAspect constructor.
     *
     * @param \LaborDigital\Typo3BetterApi\TypoContext\TypoContext    $context
     * @param \LaborDigital\Typo3BetterApi\TypoContext\Facet\PidFacet $facet
     */
    public function __construct(PidFacet $facet)
    {
        $this->facet = $facet;
    }
    
    /**
     * @inheritDoc
     */
    public function get(string $name)
    {
        if ($name === 'FACET') {
            return $this->facet;
        }
        return $this->handleGet($name);
    }
    
    /**
     * Returns true if the pid with the given key exists
     *
     * @param string $key A key like "myKey" or "storage.myKey" for hierarchical data
     *
     * @return bool
     * @deprecated will be removed in v10 -> Use PidFacet instead
     */
    public function hasPid(string $key): bool
    {
        return $this->facet->has($key);
    }
    
    /**
     * Sets the given pid for the defined key for the current runtime.
     * Note: The mapping will not be persisted!
     *
     * @param string $key A key like "myKey", "$pid.storage.stuff" or "storage.myKey" for hierarchical data
     * @param int    $pid The numeric page id which should be returned when the given pid is required
     *
     * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\PidAspect
     * @deprecated will be removed in v10 -> Use PidFacet instead
     */
    public function setPid(string $key, int $pid): PidAspect
    {
        $this->facet->set($key, $pid);
        return $this;
    }
    
    /**
     * Returns the pid for the given key
     *
     * @param string $key      A key like "myKey", "$pid.storage.stuff" or "storage.myKey" for hierarchical data
     *                         If a key is numeric and can be parsed as integer it will be returned if no
     *                         pid could be found
     * @param int    $fallback An optional fallback which will be returned, if the required pid was not found
     *                         NOTE: If no fallback is defined (-1) the method will throw an exception if the
     *                         pid was not found in the registry
     *
     * @return int
     * @throws \LaborDigital\Typo3BetterApi\Pid\InvalidPidException
     * @deprecated will be removed in v10 -> Use PidFacet instead
     */
    public function getPid(string $key, int $fallback = -1): int
    {
        return $this->facet->get($key, $fallback);
    }
    
    /**
     * Returns the whole list of all registered pid's by their keys
     * @return array
     * @deprecated will be removed in v10 -> Use PidFacet instead
     */
    public function getAllPids(): array
    {
        return $this->facet->getAll();
    }
    
    /**
     * Returns the current page's pid
     *
     * Note: in the backend you will only
     * find a page id if you are in the "page" module. If the page uid could not
     * be found the method will return 0
     *
     * @return int
     * @throws \Exception
     * @deprecated will be removed in v10 -> Use PidFacet instead
     */
    public function getCurrentPid(): int
    {
        return $this->facet->getCurrent();
    }
    
    /**
     * Internal helper to completely replace the pid array.
     * If you use this, use it with care!
     *
     * @param array $pids
     *
     * @deprecated will be removed in v10 -> Use PidFacet instead
     */
    public function __setPids(array $pids): void
    {
        $this->facet->__setAll($pids);
    }
}
