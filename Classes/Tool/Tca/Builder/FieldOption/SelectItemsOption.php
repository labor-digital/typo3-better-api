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
 * Last modified: 2021.10.25 at 17:59
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

/**
 * Applies the "items" option for a select field.
 *
 * NOTE: You can prove an array as "label" value to define two special cases.
 * The first entry ($label[0]) MUST ALWAYS be the label to be displayed.
 * The second entry ($label[1]) can be either one of these:
 * A.) A string that provides an icon identifier
 * B.) TRUE if you want to create an "option group" with this label as headline.
 */
class SelectItemsOption extends AbstractOption
{
    /**
     * If set to an array through the constructor, the items are statically defined, otherwise an "items" option will be added
     * to the definition array
     *
     * @var array|null
     */
    protected $items;
    
    public function __construct(?array $items = null)
    {
        $this->items = $items;
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        if ($this->items !== null) {
            return;
        }
        
        $definition['items'] = [
            'type' => 'array',
            'default' => [],
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        $items = $this->items ?? $options['items'] ?? [];
        if (empty($items) || ! is_array($items)) {
            return;
        }
        
        $itemsFiltered = [];
        
        foreach ($items as $k => $v) {
            if (is_array($v)) {
                // Ignore invalid configuration
                if (! isset($v[0], $v[1]) || (! is_string($v[1]) && ! is_bool($v[1]))) {
                    continue;
                }
                
                // Handle specials
                if ($v[1] === true) {
                    // Handle an option group
                    $v = [$v[0] ?? '', '--div--'];
                } elseif (is_string($v[1])) {
                    // Handle an icon identifier
                    $v = [$v[0], $k, $v[1]];
                } else {
                    continue;
                }
            } else {
                $v = [$v, $k];
            }
            
            $itemsFiltered[] = $v;
        }
        
        $config['items'] = $itemsFiltered;
    }
    
}