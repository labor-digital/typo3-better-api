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
 * Last modified: 2021.06.04 at 16:20
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfig\Traits;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use Neunerlei\Configuration\State\ConfigState;

/**
 * Trait DelayedConfigExecutionTrait
 *
 * Helper trait that will come in handy if you want to utilize the ext config handler as a "collector" of classes,
 * that have to be executed sometime later. A good example usage are the TCA classes, they are collected when the
 * ext config is generated, but only executed if and when TYPO3 actually builds the TCA.
 *
 * @package LaborDigital\T3ba\ExtConfig\Traits
 */
trait DelayedConfigExecutionTrait
{
    
    /**
     * This method is used to save a given class for a later point in time.
     *
     * IMPORTANT: If you use $groupKey, use it for ALL classes in the list or for NO class in the list.
     * You currently can't mix mix entries with and without a group key together.
     *
     * @param   ExtConfigContext  $context         The execution namespace is extracted from this context
     * @param   string            $storageKey      The list of classes get stored using this key on $state
     * @param   string            $className       The name of the class to store for a later point
     * @param   string|null       $groupKey        Optional group key if you are using a grouped config handler
     * @param   array|null        $additionalData  Optional, additional data that will be stored
     */
    protected function saveDelayedConfig(
        ExtConfigContext $context,
        string $storageKey,
        string $className,
        ?string $groupKey = null,
        ?array $additionalData = null
    ): void
    {
        $state = $context->getState();
        $list = $this->getDelayedConfig($state, $storageKey);
        $entry = [
            'namespace' => $context->getNamespace(),
            'className' => $className,
            'additionalData' => $additionalData,
        ];
        
        if ($groupKey) {
            $list[$groupKey][] = $entry;
        } else {
            $list[] = $entry;
        }
        
        $state->setAsJson($storageKey, $list);
    }
    
    /**
     * Retrieves the stored config class list from the given state
     *
     * @param   ConfigState  $state       The state object to read the class list from
     * @param   string       $storageKey  The storage key where the class list is stored
     *
     * @return array
     */
    public function getDelayedConfig(ConfigState $state, string $storageKey): array
    {
        $loadable = $state->get($storageKey);
        
        return is_string($loadable) ? SerializerUtil::unserializeJson($loadable) : [];
    }
    
    /**
     * Loads the stored config class list and executes a given callback for each entry.
     * It will also configure the given $context object to run in the same namespace it did when
     * the executed class was saved.
     *
     * If the class was stored with a group key, the callback receives the following arguments:
     * $callback($className, $groupKey, $indexInList, $additionalData)
     *
     * If the class was stored without a group key it will receive:
     * $callback($className, $groupKey, $indexInList, $additionalData)
     *
     * @param   ConfigState       $state               The state object to read the class list from
     * @param   ExtConfigContext  $context             The context object to restore to the state when the config class
     *                                                 was saved
     * @param   string            $storageKey          The storage key where the class list is stored
     * @param   callable          $callback            The callback to execute for each item
     * @param   callable|null     $afterGroupCallback  Optional, additional callback that is executed once after every
     *                                                 group that got executed
     */
    public function runDelayedConfig(
        ConfigState $state,
        ExtConfigContext $context,
        string $storageKey,
        callable $callback,
        ?callable $afterGroupCallback = null
    ): void
    {
        $list = $this->getDelayedConfig($state, $storageKey);
        
        if (empty($list)) {
            return;
        }
        
        $hasGroupKey = ! isset(reset($list)['namespace']);
        
        if ($hasGroupKey) {
            foreach ($list as $groupKey => $groupList) {
                foreach ($groupList as $index => $entry) {
                    $context->runWithNamespace($entry['namespace'],
                        static function () use ($entry, $callback, $groupKey, $index) {
                            $callback($entry['className'], $groupKey, $index, $entry['additionalData']);
                        });
                }
                if (is_callable($afterGroupCallback)) {
                    $afterGroupCallback($groupKey);
                }
            }
        } else {
            foreach ($list as $index => $entry) {
                $context->runWithNamespace($entry['namespace'],
                    static function () use ($entry, $callback, $index) {
                        $callback($entry['className'], $index, $entry['additionalData']);
                    });
            }
        }
    }
}
