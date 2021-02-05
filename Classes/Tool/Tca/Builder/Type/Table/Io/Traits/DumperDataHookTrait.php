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
 * Last modified: 2021.02.02 at 20:34
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits;


use LaborDigital\T3BA\Tool\DataHook\DataHookTypes;

trait DumperDataHookTrait
{
    protected $dataHookFieldCache = [];
    protected $dataHookTableCache = [];

    /**
     * Clears the internal caching properties
     */
    protected function clearDataHookCache(): void
    {
        $this->dataHookFieldCache = [];
        $this->dataHookTableCache = [];
    }

    /**
     * Extracts the data hook definition from the given TCA and storing them for later use
     *
     * @param   array  $tca
     */
    protected function extractDataHooksFromTca(array &$tca): void
    {
        // Extract column data hooks
        if ($tca['columns'] && is_array($tca['columns'])) {
            foreach ($tca['columns'] as $column => &$def) {
                $this->iterateDataHooksIn($def, function ($type, $key, $hook) use ($column) {
                    $this->dataHookFieldCache[$column][$type][$key] = $hook;
                });
            }
            unset($def);
        }

        // Extract table data hooks
        $this->iterateDataHooksIn($tca, function ($type, $key, $hook) {
            $this->dataHookTableCache[$type][$key] = $hook;
        });

        // Extract type data hooks
        if ($tca['types'] && is_array($tca['types'])) {
            foreach ($tca['types'] as &$def) {
                $this->iterateDataHooksIn($def, function ($type, $key, $hook) {
                    $this->dataHookTableCache[$type][$key] = $hook;
                });
            }
            unset($def);
        }
    }

    /**
     * The reverse of extractDataHooksFromTca(). It re-injects all currently cached
     * data hooks into the given tca array.
     *
     * @param   array  $tca
     */
    protected function injectDataHooksIntoTca(array &$tca): void
    {
        // Inject the data hooks back into the columns
        foreach ($this->dataHookFieldCache as $column => $hooks) {
            if (! $tca['columns'][$column] || ! is_array($tca['columns'][$column])) {
                continue;
            }
            $tca['columns'][$column][DataHookTypes::TCA_DATA_HOOK_KEY] = array_map('array_values', $hooks);
        }

        // Inject the table data hooks into the table
        $tca[DataHookTypes::TCA_DATA_HOOK_KEY] = array_map('array_values', $this->dataHookTableCache);

        $this->clearDataHookCache();
    }

    /**
     * Internal helper to iterate the data hook array
     *
     * @param   array     $configuration
     * @param   callable  $callback
     */
    protected function iterateDataHooksIn(array &$configuration, callable $callback): void
    {
        if (! $configuration[DataHookTypes::TCA_DATA_HOOK_KEY]
            || ! is_array($configuration[DataHookTypes::TCA_DATA_HOOK_KEY])) {
            return;
        }

        foreach ($configuration[DataHookTypes::TCA_DATA_HOOK_KEY] as $type => $hooks) {
            if (! is_array($hooks)) {
                continue;
            }

            foreach ($hooks as $hook) {
                if (! is_array($hook)) {
                    continue;
                }

                $key = md5(serialize($hook));
                $callback($type, $key, $hook);
            }
        }

        unset($configuration[DataHookTypes::TCA_DATA_HOOK_KEY]);
    }
}
