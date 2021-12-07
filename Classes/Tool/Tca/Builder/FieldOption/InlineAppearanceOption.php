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
 * Last modified: 2021.11.08 at 18:36
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;


class InlineAppearanceOption extends AbstractOption
{
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $def = [
            'type' => 'bool',
            'default' => false,
        ];
        
        $definition['allOpen'] = $def;
        $definition['openMultiple'] = $def;
        $definition['noSorting'] = $def;
        $definition['noDelete'] = $def;
        $definition['noHide'] = $def;
        $definition['noInfo'] = $def;
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        $config['appearance']['collapseAll'] = ! $options['allOpen'];
        $config['appearance']['expandSingle'] = ! $options['openMultiple'];
        $config['appearance']['useSortable'] = ! $options['noSorting'];
        $config['appearance']['enabledControls']['sort'] = ! $options['noSorting'];
        $config['appearance']['enabledControls']['dragdrop'] = ! $options['noSorting'];
        $config['appearance']['enabledControls']['delete'] = ! $options['noDelete'];
        $config['appearance']['enabledControls']['hide'] = ! $options['noHide'];
        $config['appearance']['enabledControls']['info'] = ! $options['noInfo'];
    }
    
}