<?php
/*
 * Copyright 2022 LABOR.digital
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
 * Last modified: 2022.01.19 at 11:31
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfig\Adapter;


use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\State\ConfigState;

class ConfigStateAdapter extends ConfigState
{
    /**
     * Allows the code to remove all currently existing data from the given state and reset it with the provided data
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $state   The state to reset to the values of $source
     * @param   \Neunerlei\Configuration\State\ConfigState  $source  The state to mirror to $state
     *
     * @return void
     */
    public static function resetState(ConfigState $state, ConfigState $source): void
    {
        $oldState = $state->getAll();
        $state->state = $source->getAll();
        $state->watchers = Arrays::merge($state->watchers, $source->watchers, 'nn');
        
        foreach ($state->watchers as $key => $watchers) {
            $oldValue = Arrays::getPath($oldState, $key);
            $value = Arrays::getPath($state->state, $key);
            
            if (SerializerUtil::serializeJson($oldValue) === SerializerUtil::serializeJson($value)) {
                continue;
            }
            
            foreach ($watchers as $watcher) {
                $watcher($value);
            }
        }
    }
}