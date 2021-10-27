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
 * Last modified: 2021.10.26 at 10:30
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

/**
 * Adds an option to apply filter callbacks to the TYPO3 element browser.
 * This can be used for "group" relation fields
 *
 * A list of filter functions to apply for this group.
 * The filter should be supplied like a typical TYPO3 callback
 * class->function. If the filter is given as array, the first value will be
 * used as callback and the second as parameters. Note: This feature is not
 * implemented in the element browser for Flex forms in the TYPO3 core... The
 * filtering of the element browser only works for TCA fields!
 */
class ElementBrowserFilterOption extends AbstractOption
{
    /**
     * Allows you to change the option name to something other than "filters"
     *
     * @var string
     */
    protected $optionName;
    
    public function __construct(?string $optionName = null)
    {
        $this->optionName = $optionName ?? 'filters';
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition[$this->optionName] = [
            'type' => 'array',
            'default' => [],
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        if (empty($options[$this->optionName])) {
            return;
        }
        
        foreach ($options[$this->optionName]['filters'] as $filter) {
            if (! is_array($filter)) {
                $filter = [$filter, []];
            }
            
            $filters[] = [
                'userFunc' => $filter[0],
                'parameters' => $filter[1],
            ];
        }
        
        $config['filter'] = $filters;
    }
    
}