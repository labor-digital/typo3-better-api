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
 * Last modified: 2021.11.19 at 16:47
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\FormEngine\Addon;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Event\FormEngine\FormFilterEvent;
use LaborDigital\T3ba\Tool\Database\BetterQuery\Util\RecursivePidResolver;
use LaborDigital\T3ba\Tool\TypoContext\StaticTypoContextAwareTrait;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Backend\Form\FormDataProvider\AbstractItemProvider;

class PidInWhereClauseResolver implements NoDiInterface
{
    use StaticTypoContextAwareTrait;
    
    protected static $markerReplacer;
    
    /**
     * Semi-public "api" to add new option keys, should I have forgotten something
     *
     * @var string[]
     */
    public static $configKeys
        = [
            'foreign_table_where',
            'where',
        ];
    
    /**
     * Checks if the foreign_table_where string of a column contains a ###PID_LIST()
     * definition, which was injected by LimitToPidsOption and if so, resolves the pids
     * using the current page context
     *
     * @param   \LaborDigital\T3ba\Event\FormEngine\FormFilterEvent  $event
     */
    public static function onFormFilter(FormFilterEvent $event): void
    {
        $data = $event->getData();
        
        foreach ($data['processedTca']['columns'] as $column => &$config) {
            foreach (static::$configKeys as $configKey) {
                if (! is_string($config['config'][$configKey] ?? null)) {
                    continue;
                }
                
                $where = $config['config'][$configKey];
                
                if (empty($where)) {
                    continue;
                }
                
                if (str_contains($where, '###PID_LIST(')) {
                    $where = static::resolvePidList($where);
                }
                
                if (str_contains($where, '###PIDS_RECURSIVE(')) {
                    $where = static::resolveRecursivePidList($where, $column, $data);
                }
                
                $config['config'][$configKey] = $where;
            }
        }
        unset($config);
        
        $event->setData($data);
    }
    
    protected static function resolvePidList(string $where): string
    {
        return preg_replace_callback('~###PID_LIST\((.*?)\)###~', function ($m) {
            if (empty($m[1])) {
                return '0';
            }
            
            $pids = Arrays::makeFromStringList((string)$m[1]);
            if (empty($pids)) {
                return '0';
            }
            
            return implode(',', static::getTypoContext()->pid()->getMultiple($pids));
        }, $where);
    }
    
    protected static function resolveRecursivePidList(string $where, string $column, array $data): string
    {
        return preg_replace_callback('~###PIDS_RECURSIVE\((.*?)\)###~', function ($m) use ($column, $data) {
            if (empty($m[1])) {
                return $m[1];
            }
            
            $query = (string)$m[1];
            [$depth, $selector] = explode('|', $query);
            
            if (str_contains((string)$selector, '###')) {
                $selector = static::getMarkerReplacer()->replaceMarkers($selector, $column, $data);
            }
            
            $pidList = Arrays::makeFromStringList((string)$selector);
            
            if (empty($pidList)) {
                return '0';
            }
            
            $pidList = static::getTypoContext()->di()->makeInstance(RecursivePidResolver::class)
                             ->resolve($pidList, (int)$depth);
            
            if (empty($pidList)) {
                return '0';
            }
            
            return implode(',', $pidList);
        }, $where);
    }
    
    /**
     * Utilizes the AbstractItemProvider in order to create our internal adapter
     * to reliably replace the markers inside a given query. We need this to resolve the pids recursively.
     */
    protected static function getMarkerReplacer()
    {
        if (isset(static::$markerReplacer)) {
            return static::$markerReplacer;
        }
        
        return static::$markerReplacer = new class extends AbstractItemProvider {
            public function replaceMarkers(string $where, string $column, array $data): string
            {
                $data['processedTca']['columns'][$column]['config']['foreign_table_where'] = $where;
                $parsed = $this->processForeignTableClause($data, $data['tableName'], $column);
                
                return $parsed['WHERE'];
            }
        };
    }
}