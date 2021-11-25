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
 * Last modified: 2021.11.19 at 18:18
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Pid;


use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\Inflection\Inflector;

class ConfigGenerator implements PublicServiceInterface
{
    /**
     * Processes the config state and merges both the site-based and global pids
     * into a unified structure, before dumping the required typoScript configuration
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $state
     */
    public function finalizePidConfig(ConfigState $state): void
    {
        $pidList = $state->get('t3ba.pids', []);
        
        $sitePids = [];
        foreach ($state->get('typo.site', []) as $siteIdentifier => $site) {
            if (! is_array($site['pids'] ?? null)) {
                continue;
            }
            
            $sitePids[$siteIdentifier] = $this->createSitePidOverlay($pidList, $site['pids']);
            $state->set('typo.site.' . $siteIdentifier . '.pids', $sitePids[$siteIdentifier]);
        }
        
        $state->set('t3ba.pids', $pidList);
        
        $ts = $this->dumpTypoScript($pidList, $sitePids);
        $state->useNamespace('typo.typoScript.dynamicTypoScript', static function () use ($state, $ts) {
            $state->set('pids\\.constants', $ts['constants']);
            $state->set('pids\\.setup', $ts['setup']);
        });
    }
    
    /**
     * Creates an overlay for the site pid list by merging common elements into the global
     * pids array.
     *
     * @param   array  $pidList
     * @param   array  $sitePidList
     *
     * @return array
     */
    protected function createSitePidOverlay(array &$pidList, array $sitePidList): array
    {
        $pidList = $this->mergeUnknownIntoList($pidList, $sitePidList);
        
        return $sitePidList;
    }
    
    /**
     * Merges keys that exist in $b but not in $a to $a, automatically removing them in $b while doing so.
     * Keys of $b, that already exist in $a, but differ in their value will stay in $b,
     * while keys with the same value in $a and $b will be removed from $b, too.
     *
     * @param   array  $a
     * @param   array  $b
     *
     * @return array
     */
    protected function mergeUnknownIntoList(array $a, array &$b): array
    {
        $_b = [];
        
        foreach ($b as $k => $v) {
            if (! isset($a[$k])) {
                $a[$k] = $v;
                continue;
            }
            
            if ($a[$k] === $v) {
                continue;
            }
            
            if (is_array($v) && is_array($a[$k] ?? null)) {
                $a[$k] = $this->mergeUnknownIntoList($a[$k], $v);
                if (! empty($v)) {
                    $_b[$k] = $v;
                }
                continue;
            }
            
            $_b[$k] = $v;
        }
        
        $b = array_filter($_b);
        
        return $a;
    }
    
    /**
     * Generates the TypoScript configuration to be injected into the config state
     *
     * @param   array  $pids
     * @param   array  $sitePids
     *
     * @return array
     */
    protected function dumpTypoScript(array $pids, array $sitePids): array
    {
        $constants = [];
        $constants[] = $this->dumpTypoScriptConstantPidList($pids);
        
        foreach ($sitePids as $siteIdentifier => $_pids) {
            if (empty($_pids)) {
                continue;
            }
            
            $constants[] = '[betterSite("identifier") == "' . $siteIdentifier . '"]';
            $constants[] = $this->dumpTypoScriptConstantPidList($_pids, false);
            $constants[] = '[end]';
        }
        
        return [
            'setup' => $this->dumpTypoScriptSetupMapping($pids),
            'constants' => implode(PHP_EOL, $constants),
        ];
    }
    
    /**
     * Builds the typoScript declaration of constants for the given list of pids
     *
     * @param   array  $pids
     * @param   bool   $addCategoryDefinition
     *
     * @return string
     */
    protected function dumpTypoScriptConstantPidList(array $pids, bool $addCategoryDefinition = true): string
    {
        $list = [];
        foreach (Arrays::flatten($pids) as $k => $pid) {
            $key = 'config.t3ba.pid.' . $k;
            if ($addCategoryDefinition) {
                $list[] = '#cat=t3ba/pid; type=int+; label=Page ID ' . Inflector::toHuman($k);
            }
            $list[] = $key . '=' . $pid;
        }
        
        return implode(PHP_EOL, $list);
    }
    
    protected function dumpTypoScriptSetupMapping(array $pids): string
    {
        $list = [];
        foreach (Arrays::flatten($pids) as $k => $pid) {
            $key = 'config.t3ba.pid.' . $k;
            $list[] = $key . '={$' . $key . '}';
        }
        
        return implode(PHP_EOL, $list);
    }
}