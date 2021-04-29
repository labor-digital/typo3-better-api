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
 * Last modified: 2021.04.29 at 22:17
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
 * Last modified: 2020.03.19 at 13:54
 */

namespace LaborDigital\T3BA\FormEngine\Addon;

use LaborDigital\T3BA\Core\Di\StaticContainerAwareTrait;
use LaborDigital\T3BA\Event\DataHandler\DataHandlerDefaultFilterEvent;
use LaborDigital\T3BA\Event\FormEngine\BackendFormNodeFilterEvent;
use LaborDigital\T3BA\Event\FormEngine\FormFilterEvent;
use LaborDigital\T3BA\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3BA\Tool\Tca\TcaUtil;

class FieldDefaultAndPlaceholderTranslation
{
    use StaticContainerAwareTrait;
    
    /**
     * We have to make sure that default values in the form engine get translated
     *
     * @param   \LaborDigital\T3BA\Event\FormEngine\FormFilterEvent  $event
     */
    public static function onFormFilter(FormFilterEvent $event): void
    {
        $data = $event->getData();
        
        $data['databaseRow'] = static::processDefaults($data['databaseRow'], $data['processedTca']);
        
        $event->setData($data);
    }
    
    /**
     * This applier is used to translate the "placeholder" value of form elements.
     *
     * @param   \LaborDigital\T3BA\Event\FormEngine\BackendFormNodeFilterEvent  $event
     */
    public static function onNodeFilter(BackendFormNodeFilterEvent $event): void
    {
        $config = $event->getProxy()->getConfig();
        
        if (is_string($config['placeholder'] ?? null)) {
            $event->getProxy()->setConfig(array_merge(
                $config,
                [
                    // @todo translate be when implemented
                    'placeholder' => static::cs()->translator->translate($config['placeholder']),
                ]
            ));
        }
    }
    
    /**
     * Handles the default data generation in the data handler
     *
     * @param   \LaborDigital\T3BA\Event\DataHandler\DataHandlerDefaultFilterEvent  $event
     */
    public static function onDefaultFilter(DataHandlerDefaultFilterEvent $event): void
    {
        TcaUtil::runWithResolvedTypeTca($event->getRow(), $event->getTableName(), static function () use ($event) {
            $row = static::processDefaults($event->getRow(), $GLOBALS['TCA'][$event->getTableName()] ?? []);
            $event->setRow($row);
        });
    }
    
    /**
     * Helper to replace both callback defaults and translatable defaults in the data handler and the form engine
     *
     * @param   array  $row
     * @param   array  $tca
     *
     * @return array
     */
    protected static function processDefaults(array $row, array $tca): array
    {
        foreach ($tca['columns'] as $key => $column) {
            if (is_string($column['config']['default'] ?? null) && $row[$key] === $column['config']['default']) {
                $value = $row[$key];
                
                if (empty($value) || ! is_string($value)) {
                    continue;
                }
                
                // Resolve callback defaults
                if (str_starts_with($value, '@callback:')) {
                    $row[$key] = call_user_func(
                        NamingUtil::resolveCallable(substr($value, 10)),
                        $key, $row, $value, $column
                    );
                    continue;
                }
                
                // Resolve translatable defaults
                if (str_starts_with($value, 'LLL:') || strpos($value, '.') !== false) {
                    $row[$key] = static::cs()->translator->translate($value);
                }
            }
        }
        
        return $row;
    }
}
