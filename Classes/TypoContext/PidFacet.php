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

namespace LaborDigital\T3ba\TypoContext;

use LaborDigital\T3ba\ExtConfig\Traits\SiteConfigAwareTrait;
use LaborDigital\T3ba\Tool\TypoContext\FacetInterface;
use LaborDigital\T3ba\Tool\TypoContext\InvalidPidException;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use LaborDigital\T3ba\TypoContext\Util\CurrentPidFinder;
use Neunerlei\Arrays\Arrays;
use RuntimeException;
use Throwable;

/**
 * Repository of information about registered PIDs and the local page id
 */
class PidFacet implements FacetInterface
{
    use SiteConfigAwareTrait;
    
    /**
     * The list of GLOBAL pids inherited from the config state
     *
     * @var array
     */
    protected $pids;
    
    /**
     * The list of pids that have been set at runtime (by their site identifier)
     *
     * @var array
     */
    protected $setPids = [];
    
    /**
     * The list of resolved pids by their site identifier.
     * The resolved pid list contains the GLOBAL pids, combined with the SITE pids and the SET_PIDS
     * for a specified site.
     *
     * @var array
     */
    protected $resolvedPids = [];
    
    /**
     * PidFacet constructor.
     *
     * @param   TypoContext  $context
     */
    public function __construct(TypoContext $context)
    {
        $this->context = $context;
        
        $resetter = function ($v) {
            $this->resolvedPids = [];
            
            return $v;
        };
        
        $this->registerCachedProperty('pids', 't3ba.pids', $context->config()->getConfigState(), $resetter, []);
        $this->registerConfig('pids', $resetter);
    }
    
    /**
     * @inheritDoc
     */
    public static function getIdentifier(): string
    {
        return 'pid';
    }
    
    /**
     * Returns true if the pid with the given key exists
     *
     * @param   string       $key             A key like "myKey" or "storage.myKey" for hierarchical data
     * @param   string|null  $siteIdentifier  Optional site identifier to request the pids for a
     *                                        specific site and not for the currently active one.
     *
     * @return bool
     */
    public function has(string $key, ?string $siteIdentifier = null): bool
    {
        return Arrays::hasPath($this->getResolvedPids($siteIdentifier), $this->stripPrefix($key));
    }
    
    /**
     * Sets the given pid for the defined key for the current runtime.
     * Note: The mapping will not be persisted!
     *
     * @param   string       $key             A key like "myKey", "$pid.storage.stuff" or "storage.myKey" for hierarchical data
     * @param   int          $pid             The numeric page id which should be returned when the given pid is required
     * @param   string|null  $siteIdentifier  Optional site identifier to request the pids for a
     *                                        specific site and not for the currently active one.
     *
     * @return $this
     */
    public function set(string $key, int $pid, ?string $siteIdentifier = null): self
    {
        $siteIdentifier = $this->prepareSiteIdentifier($siteIdentifier);
        $this->setPids[$siteIdentifier]
            = Arrays::setPath($this->setPids[$siteIdentifier] ?? [], $this->stripPrefix($key), $pid);
        
        return $this;
    }
    
    /**
     * The same as set() but adds multiple pids at once.
     * Note: The mapping will not be persisted!
     *
     * @param   array        $pids            A list of pids as $path => $pid or as multidimensional array
     * @param   string|null  $siteIdentifier  Optional site identifier to request the pids for a
     *                                        specific site and not for the currently active one.
     *
     * @return $this
     * @throws \LaborDigital\T3ba\Tool\TypoContext\InvalidPidException
     */
    public function setMultiple(array $pids, ?string $siteIdentifier = null): self
    {
        foreach (Arrays::flatten($pids) as $k => $pid) {
            if (! is_string($k)) {
                throw new InvalidPidException('The given key for pid: ' . $pid . ' has to be a string!');
            }
            if (! is_numeric($pid)) {
                throw new InvalidPidException(
                    'The given value for pid identifier: "' . $k . '" has to be numeric! Given value: '
                    . gettype($pid));
            }
        }
        
        $siteIdentifier = $this->prepareSiteIdentifier($siteIdentifier);
        $this->setPids[$siteIdentifier]
            = Arrays::merge($this->setPids[$siteIdentifier] ?? [], $pids);
        
        return $this;
    }
    
    /**
     * Returns the pid for the given key
     *
     * @param   string|integer  $key             A key like "myKey", "$pid.storage.stuff" or "storage.myKey" for hierarchical
     *                                           data If a key is numeric and can be parsed as integer it will be returned if
     *                                           no pid could be found
     * @param   int             $fallback        An optional fallback which will be returned, if the required pid was not
     *                                           found NOTE: If no fallback is defined (-1) the method will throw an
     *                                           exception if the pid was not found in the registry
     * @param   string|null     $siteIdentifier  Optional site identifier to request the pids for a
     *                                           specific site and not for the currently active one.
     *
     * @return int
     * @throws \LaborDigital\T3ba\Tool\TypoContext\InvalidPidException
     */
    public function get($key, int $fallback = -1, ?string $siteIdentifier = null): int
    {
        if (is_int($key)) {
            return $key;
        }
        
        if (! is_string($key)) {
            throw new InvalidPidException(
                'Invalid key or pid given, only strings and integers are allowed! Given: ' . gettype($key));
        }
        
        // Numeric as string -> directly convertible to integer
        if ((int)$key . '' === $key) {
            return (int)$key;
        }
        
        $pid = Arrays::getPath($this->getResolvedPids($siteIdentifier), $this->stripPrefix($key), -9999);
        
        if (! is_numeric($pid) || $pid === -9999) {
            if ($fallback !== -1) {
                return $fallback;
            }
            if (is_numeric($key)) {
                return (int)$key;
            }
            throw new InvalidPidException('There is no registered pid for key: ' . $key);
        }
        
        return $pid;
    }
    
    /**
     * Similar to get() but returns multiple pids at once, instead of a single one
     *
     * @param   array        $keys            An array of pid keys to retrieve
     * @param   int          $fallback        An optional fallback which will be returned, if the required pid was not
     *                                        found NOTE: If no fallback is defined (-1) the method will throw an
     *                                        exception if the pid was not found in the registry
     * @param   string|null  $siteIdentifier  Optional site identifier to request the pids for a
     *                                        specific site and not for the currently active one.
     *
     * @return array
     * @see get()
     */
    public function getMultiple(array $keys, int $fallback = -1, ?string $siteIdentifier = null): array
    {
        foreach ($keys as $k => $key) {
            $keys[$k] = $this->get($key, $fallback, $siteIdentifier);
        }
        
        return $keys;
    }
    
    /**
     * Returns a subset of pids. A subset is a list of pids that are stored in the same "path".
     * For example "page.foo", "page.boo" and "page.bar" all live in the same subset of "page".
     * So you can request the subset key "page" to retrieve the list of all pids.
     *
     * @param   string       $key             The key of a subset to retrieve, use a typical path to retrieve nested subsets
     * @param   string|null  $siteIdentifier  Optional site identifier to request the pids for a
     *                                        specific site and not for the currently active one.
     *
     * @return array
     * @throws \LaborDigital\T3ba\Tool\TypoContext\InvalidPidException
     */
    public function getSubSet(string $key, ?string $siteIdentifier = null): array
    {
        $list = Arrays::getPath($this->getResolvedPids($siteIdentifier), $this->stripPrefix($key), []);
        if (! is_array($list)) {
            throw new InvalidPidException('There given key : ' . $key . ' did not resolve to a pid subset!');
        }
        
        return $list;
    }
    
    /**
     * Returns the whole list of all registered pids by their keys
     *
     * @param   string|null  $siteIdentifier  Optional site identifier to request the pids for a
     *                                        specific site and not for the currently active one.
     *
     * @return array
     */
    public function getAll(?string $siteIdentifier = null): array
    {
        return $this->getResolvedPids($siteIdentifier);
    }
    
    /**
     * Tries to find the current page uid. The method will automatically try to fall back
     *
     * to the site-root page if it could not find the current page. If neither a page nor a site root pid
     * could be found 0 will be returned.
     *
     * @return int
     * @throws \Exception
     */
    public function getCurrent(): int
    {
        return CurrentPidFinder::findPid($this->context) ?? 0;
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
        $key = trim($key);
        $prefix = substr($key, 0, 5);
        if ($prefix === '$pid.' || $prefix === '@pid.') {
            return substr($key, 5);
        }
        
        return $key;
    }
    
    /**
     * Internal helper to use either the siteIdentifier or our internal fallback
     *
     * @param   string|null  $siteIdentifier
     *
     * @return string
     */
    protected function prepareSiteIdentifier(?string $siteIdentifier = null): string
    {
        return $siteIdentifier ?? '@default';
    }
    
    /**
     * Internal helper to resolve the pids including their site-based overlays
     *
     * @param   string|null  $siteIdentifier
     *
     * @return array
     */
    protected function getResolvedPids(?string $siteIdentifier = null): array
    {
        $siteIdentifierWithFallback = $this->prepareSiteIdentifier($siteIdentifier);
        if (isset($this->resolvedPids[$siteIdentifierWithFallback])) {
            return $this->resolvedPids[$siteIdentifierWithFallback];
        }
        
        if (! is_array($this->pids)) {
            throw new RuntimeException('You are requiring the PIDs to early! They have not yet been registered!');
        }
        
        return $this->resolvedPids[$siteIdentifierWithFallback]
            = Arrays::merge(
            $this->pids,
            $this->getSiteConfig($siteIdentifier),
            $this->setPids[$siteIdentifierWithFallback] ?? [],
            'sn'
        );
    }
    
    /**
     * @inheritDoc
     */
    protected function getSiteIdentifier(): string
    {
        try {
            return $this->context->site()->getCurrent()->getIdentifier();
        } catch (Throwable $e) {
            return '@unknown';
        }
    }
    
    
}
