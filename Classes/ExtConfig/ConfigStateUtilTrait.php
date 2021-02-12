<?php
/*
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
 * Last modified: 2020.10.19 at 23:56
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig;


use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\State\ConfigState;

trait ConfigStateUtilTrait
{

    /**
     * Helper to attach the given value to a string key in the state object
     *
     * @param   ConfigState  $state  The state object to add the value to
     * @param   string       $key    The storage key to store the value at
     * @param   string       $value  The value to add
     */
    protected function attachToStringValue(ConfigState $state, string $key, string $value): void
    {
        $v = (string)$state->get($key, '');
        $state->set($key, $v . $value);
    }

    /**
     * Helper to attach a given value at the end of an array. If the given key is not yet an array,
     * it will be converted into one.
     *
     * @param   ConfigState  $state  The state object to add the value to
     * @param   string       $key    The storage key to store the value at
     * @param   mixed        $value  The value to add
     */
    protected function attachToArrayValue(ConfigState $state, string $key, $value): void
    {
        $v   = (array)$state->get($key, []);
        $v[] = $value;
        $state->set($key, $v);
    }

    /**
     * Helper to store a given $value as a json encoded value into the state object.
     * This can be helpful if you have a big data object which is only required once or twice in 100 requests,
     * so the cache can handle the value as a string and does not have to rehydrate the data on every request.
     *
     * @param   ConfigState  $state       The state object to add the value to
     * @param   string       $key         The storage key to store the value at
     * @param   mixed        $value       The value to add
     * @param   bool         $ifNotEmpty  As long as this is TRUE only non empty values are stored into the state.
     *                                    Otherwise NULL is written into the state. Set this to FALSE to force
     *                                    the method to write the value into the state even if it is empty
     */
    protected function setAsJson(ConfigState $state, string $key, array $value, bool $ifNotEmpty = true): void
    {
        if (empty($value) && ! $ifNotEmpty) {
            $state->set($key, null);

            return;
        }

        $state->set($key, Arrays::dumpToJson($value));
    }
}
