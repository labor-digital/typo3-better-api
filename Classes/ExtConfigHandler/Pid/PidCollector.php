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

namespace LaborDigital\T3ba\ExtConfigHandler\Pid;

use InvalidArgumentException;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\Interfaces\ExtConfigConfiguratorInterface;
use LaborDigital\T3ba\ExtConfig\Interfaces\ExtConfigContextAwareInterface;
use LaborDigital\T3ba\ExtConfig\Traits\ExtConfigContextAwareTrait;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\Inflection\Inflector;

class PidCollector implements ExtConfigContextAwareInterface, ExtConfigConfiguratorInterface, NoDiInterface
{
    use ExtConfigContextAwareTrait;
    
    /**
     * The list of configured pids
     *
     * @var array
     */
    protected $pids = [];
    
    /**
     * Adds a new pid mapping to the pid service.
     *
     * @param   string  $key  A key like "myKey" or "storage.myKey" for hierarchical data
     * @param   int     $pid  The numeric page id which should be returned when the given pid is required
     *
     * @return $this
     */
    public function set(string $key, int $pid): self
    {
        $this->pids = Arrays::setPath($this->pids, $this->context->replaceMarkers($key), $pid);
        
        return $this;
    }
    
    /**
     * The same as registerPid() but registers multiple pids at once
     *
     * @param   array  $pids  A list of pids as $path => $pid or as multidimensional array
     *
     * @return $this
     */
    public function setMultiple(array $pids): self
    {
        foreach (Arrays::flatten($pids) as $k => $pid) {
            if (! is_string($k)) {
                throw new InvalidArgumentException('The given key for pid: ' . $pid . ' has to be a string!');
            }
            
            if (! is_numeric($pid)) {
                throw new InvalidArgumentException(
                    'The given value for pid: ' . $k . ' has to be numeric! Given value: ' . $pid);
            }
            
            $this->set($k, (int)$pid);
        }
        
        return $this;
    }
    
    /**
     * Removes a previously configured pid pair
     *
     * @param   string  $key  A key like "myKey" or "storage.myKey" for hierarchical data to remove
     *
     * @return $this
     */
    public function remove(string $key): self
    {
        $this->pids = Arrays::removePath($this->pids, $this->context->replaceMarkers($key));
        
        return $this;
    }
    
    /**
     * Like remove() but removes multiple pid pairs at once
     *
     * @param   array  $pids  A list of keys, like "myKey" or "storage.myKey" for hierarchical data to remove
     *
     * @return $this
     */
    public function removeMultiple(array $pids): self
    {
        foreach (Arrays::flatten($pids) as $k) {
            if (! is_string($k)) {
                throw new InvalidArgumentException('The given key: ' . $k . ' has to be a string!');
            }
            
            $this->remove($k);
        }
        
        return $this;
    }
    
    /**
     * Removes ALL configured pid pairs
     *
     * @return $this
     */
    public function clear(): self
    {
        $this->pids = [];
        
        return $this;
    }
    
    /**
     * Returns all registered pids in this collector instance
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->pids;
    }
    
    /**
     * @inheritDoc
     */
    public function finish(ConfigState $state): void
    {
        // Register the pids in the state
        $state->set('pids', $this->pids);
        $state->useNamespace('typo.typoScript.dynamicTypoScript', function () use ($state) {
            $ts = $this->buildPidTypoScript();
            $state->set('pids\\.constants', $ts['constants']);
            $state->set('pids\\.setup', $ts['setup']);
        });
    }
    
    /**
     * Uses the configured list of pids and converts it into a typoScript setup and constant string
     *
     * @return array
     */
    protected function buildPidTypoScript(): array
    {
        $constantsTs = [];
        foreach (Arrays::flatten($this->pids) as $k => $pid) {
            $key = 'config.t3ba.pid.' . $k;
            $constantsTs[] = '#cat=t3ba/pid; type=int+; label=Page ID ' . Inflector::toHuman($k);
            $constantsTs[] = $key . '=' . $pid;
        }
        
        // Done
        return [
            'setup' => '# Nothing to do here',
            'constants' => implode(PHP_EOL, $constantsTs),
        ];
    }
}
