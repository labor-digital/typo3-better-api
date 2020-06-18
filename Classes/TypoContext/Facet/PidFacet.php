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
 * Last modified: 2020.05.12 at 12:58
 */

namespace LaborDigital\Typo3BetterApi\TypoContext\Facet;

use LaborDigital\Typo3BetterApi\Pid\InvalidPidException;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\Arrays\Arrays;
use Neunerlei\PathUtil\Path;
use function GuzzleHttp\Psr7\parse_query;

class PidFacet implements FacetInterface
{
    /**
     * The pid storage list
     *
     * @var array
     */
    protected $pids = [];
    
    /**
     * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
     */
    protected $context;
    
    /**
     * PidFacet constructor.
     *
     * @param   \LaborDigital\Typo3BetterApi\TypoContext\TypoContext  $context
     */
    public function __construct(TypoContext $context)
    {
        $this->context = $context;
    }
    
    /**
     * Returns true if the pid with the given key exists
     *
     * @param   string  $key  A key like "myKey" or "storage.myKey" for hierarchical data
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return Arrays::hasPath($this->pids, $this->stripPrefix($key));
    }
    
    /**
     * Sets the given pid for the defined key for the current runtime.
     * Note: The mapping will not be persisted!
     *
     * @param   string  $key  A key like "myKey", "$pid.storage.stuff" or "storage.myKey" for hierarchical data
     * @param   int     $pid  The numeric page id which should be returned when the given pid is required
     *
     * @return \LaborDigital\Typo3BetterApi\TypoContext\Facet\PidFacet
     */
    public function set(string $key, int $pid): PidFacet
    {
        $this->pids = Arrays::setPath($this->pids, $this->stripPrefix($key), $pid);
        
        return $this;
    }
    
    /**
     * Returns the pid for the given key
     *
     * @param   string  $key       A key like "myKey", "$pid.storage.stuff" or "storage.myKey" for hierarchical data
     *                             If a key is numeric and can be parsed as integer it will be returned if no
     *                             pid could be found
     * @param   int     $fallback  An optional fallback which will be returned, if the required pid was not found
     *                             NOTE: If no fallback is defined (-1) the method will throw an exception if the
     *                             pid was not found in the registry
     *
     * @return int
     * @throws \LaborDigital\Typo3BetterApi\Pid\InvalidPidException
     */
    public function get(string $key, int $fallback = -1): int
    {
        $pid = Arrays::getPath($this->pids, $this->stripPrefix($key), -9999);
        if (! is_numeric($pid) || $pid === -9999) {
            if ($fallback !== -1) {
                return $fallback;
            }
            if (is_numeric($key) && (int)$key == $key) {
                return (int)$key;
            }
            throw new InvalidPidException('There is no registered pid for key: ' . $key);
        }
        
        return $pid;
    }
    
    /**
     * Returns the whole list of all registered pid's by their keys
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->pids;
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
     */
    public function getCurrent(): int
    {
        $requestFacet = $this->context->Request();
        if ($this->context->Env()->isBackend()) {
            // BACKEND
            // ============
            // Read current ID when in backend
            if (isset($GLOBALS['TSFE']) && isset($GLOBALS['TSFE']->id)) {
                return (int)$GLOBALS['TSFE']->id;
            }
            if ($requestFacet->hasGet('id')) {
                return (int)$requestFacet->getGet('id');
            }
            if (isset($_REQUEST['id'])) {
                return (int)$_REQUEST['id'];
            }
            // Try to parse return url
            if ($requestFacet->hasGet('returnUrl')) {
                $query = Path::makeUri('http://www.foo.bar' . $requestFacet->getGet('returnUrl'))->getQuery();
                $query = parse_query($query);
                if (isset($query['id'])) {
                    return isset($query['id']);
                }
            }
        } else {
            // FRONTEND
            // ============
            // Read current id when in frontend
            if (isset($GLOBALS['TSFE']->id)) {
                return $GLOBALS['TSFE']->id;
            }
            if ($requestFacet->hasGet('id')) {
                return (int)$requestFacet->getGet('id');
            }
        }
        
        // Fallback to the root pid of the site
        if ($this->context->Site()->exists()) {
            return $this->context->Site()->get()->getRootPageId();
        }
        
        return 0;
    }
    
    /**
     * Internal helper to completely replace the pid array.
     * If you use this, use it with care!
     *
     * @param   array  $pids
     */
    public function __setAll(array $pids): void
    {
        $this->pids = $pids;
    }
    
    /**
     * Internal helper to make sure there is no $pid, (at)pid (stupid annotation parsing...) prefix in the given keys
     *
     * @param   string  $key
     *
     * @return string
     */
    protected function stripPrefix(string $key): string
    {
        $key    = trim($key);
        $prefix = substr($key, 0, 5);
        if ($prefix === '$pid.' || $prefix === '@pid.') {
            return substr($key, 5);
        }
        
        return $key;
    }
}
