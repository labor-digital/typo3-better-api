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
 * Last modified: 2021.01.28 at 13:38
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits;


use LaborDigital\T3BA\Tool\DataHook\DataHookTypes;

trait DumperMergingTrait
{

    /**
     * Makes sure that all columns that are defined in the original TCA are merged back
     * into the new, generated TCA in order to incorporate all, possibly not loaded fields
     *
     * @param   array  $initialTca
     * @param   array  $newTca
     */
    protected function mergeMissingColumns(array $initialTca, array &$newTca): void
    {
        $this->mergeMissingElements('columns', $initialTca, $newTca);
    }

    /**
     * Makes sure that all types that are defined in the original TCA are merged back
     * into the new, generated TCA in order to incorporate all, possibly not loaded types
     *
     * @param   array  $initialTca
     * @param   array  $newTca
     */
    protected function mergeMissingTypes(array $initialTca, array &$newTca): void
    {
        $this->mergeMissingElements('types', $initialTca, $newTca);
    }

    /**
     * Internal helper to merge missing elements from the initial tca into the new tca
     *
     * @param   string  $slot
     * @param   array   $initialTca
     * @param   array   $newTca
     */
    protected function mergeMissingElements(string $slot, array $initialTca, array &$newTca): void
    {
        $els = $newTca[$slot] ?? [];
        foreach ($initialTca[$slot] ?? [] as $k => $v) {
            if (! isset($els[$k])) {
                $els[$k] = $v;
            }
        }
        $newTca[$slot] = $els;
    }

    /**
     * Merges the data hook definitions of a type and a table into each other, by comparing their type constraints
     *
     * @param   array  $tca
     * @param   array  $typeTca
     */
    protected function mergeTableDataHooks(array &$tca, array $typeTca): void
    {
        $hookKey = DataHookTypes::TCA_DATA_HOOK_KEY;

        if (isset($typeTca[$hookKey])) {
            // The tca does not have a data hook array -> Set it and be done
            if (! isset($tca[$hookKey])) {
                $tca[$hookKey] = $typeTca[$hookKey];

                return;
            }

            /**
             * Small helper to convert a hook array into a simply comparable string representation
             *
             * @param   string  $type
             * @param   array   $hook
             * @param   bool    $dropConstraints
             *
             * @return string
             */
            $hookSerializer = static function (string $type, array $hook, bool $dropConstraints = false): string {
                /** @noinspection JsonEncodingApiUsageInspection */
                return $type . ',' . $hook[0][0] . '::' . $hook[0][1] . ',' .
                       @json_encode($dropConstraints ? ['constraints' => []] : $hook[1]);
            };

            // Generate a easily comparable list of hooks in the default type
            $tcaHooks = [];
            foreach ($tca[$hookKey] as $type => $hooks) {
                foreach ($hooks as $hook) {
                    $tcaHooks[] = $hookSerializer($type, $hook);
                }
            }

            // Iterate all hooks and check if the default TCA already has them
            foreach ($typeTca[$hookKey] as $type => $hooks) {
                foreach ($hooks as $hook) {
                    if (in_array($hookSerializer($type, $hook), $tcaHooks, true)
                        || in_array($hookSerializer($type, $hook, true), $tcaHooks, true)) {
                        continue;
                    }

                    // Inherit the hook
                    $tca[$hookKey][$type][] = $hook;
                }
            }
        }
    }
}
