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
 * Last modified: 2021.11.29 at 20:19
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Database\BetterQuery\Util;


use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\Database\QueryGenerator;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Type\Bitmask\Permission;

class RecursivePidResolver implements PublicServiceInterface, SingletonInterface
{
    /**
     * The list of resolved pids for their input values
     *
     * @var array
     */
    protected $cache = [];
    
    /**
     * @var \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     */
    protected $context;
    
    public function __construct(TypoContext $context)
    {
        $this->context = $context;
    }
    
    /**
     * Resolves the list of given pids recursively and returns the list with
     * the child pids attached to it
     *
     * @param   array  $pids            The list of pids to resolve child pids for
     * @param   int    $recursionDepth  The depth up to which child pages should be resolved
     *
     * @return array
     */
    public function resolve(array $pids, int $recursionDepth = 0): array
    {
        if (empty($pids)) {
            return [];
        }
        
        sort($pids);
        $cacheKey = md5(implode(',', $pids) . '-' . $recursionDepth);
        
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        return $this->cache[$cacheKey] = $this->resolveWithoutCache($pids, $recursionDepth);
    }
    
    /**
     * Resets the cache back to an empty state
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
    
    /**
     * Internal implementation to resolve the recursive pids based without checking the cache
     *
     * @param   array  $pids
     * @param   int    $recursionDepth
     *
     * @return array
     */
    protected function resolveWithoutCache(array $pids, int $recursionDepth = 0): array
    {
        if ($this->context->env()->isFrontend()) {
            return $this->resolveFrontendPids($pids, $recursionDepth);
        }
        
        return $this->resolveBackendPids($pids, $recursionDepth);
    }
    
    /**
     * Frontend only resolution of the treeList through the current content object renderer
     *
     * @param   array  $pids
     * @param   int    $recursionDepth
     *
     * @return array
     */
    protected function resolveFrontendPids(array $pids, int $recursionDepth = 0): array
    {
        return $this->processPidRequest($pids, $recursionDepth, function (int $pid, int $recursionDepth) {
            return $this->context->di()->cs()->tsfe
                ->getContentObjectRenderer()->getTreeList($pid, $recursionDepth);
        });
    }
    
    /**
     * Backend only resolution of the pids through the backend user authentication
     *
     * @param   array  $pids
     * @param   int    $recursionDepth
     *
     * @return array
     */
    protected function resolveBackendPids(array $pids, int $recursionDepth = 0): array
    {
        $permsClause = $this->context->beUser()->getUser()->getPagePermsClause(Permission::PAGE_SHOW);
        $queryGenerator = $this->context->di()->makeInstance(QueryGenerator::class);
        
        return $this->processPidRequest($pids, $recursionDepth,
            static function (int $pid, int $recursionDepth)
            use ($queryGenerator, $permsClause) {
                return $queryGenerator->getTreeList($pid, $recursionDepth, 0, $permsClause);
            }
        );
    }
    
    /**
     * Internal helper to handle the result of the tree list implementations
     *
     * @param   array     $pids
     * @param   int       $recursionDepth
     * @param   callable  $callback
     *
     * @return array
     */
    protected function processPidRequest(array $pids, int $recursionDepth, callable $callback): array
    {
        if ($recursionDepth <= 0) {
            return $pids;
        }
        
        $list = [];
        foreach ($pids as $startPid) {
            $_pids = $callback(-(int)$startPid, $recursionDepth);
            $list[] = Arrays::makeFromStringList($_pids);
        }
        
        return array_unique(array_merge([], ...$list));
    }
}