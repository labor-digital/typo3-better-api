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
 * Last modified: 2020.08.23 at 23:23
 */

namespace LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin;

use LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Flex;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Io\Factory;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Io\MissingFlexFormFileException;
use Neunerlei\Inflection\Inflector;

class PluginConfigurator extends AbstractElementConfigurator
{
    
    /**
     * Holds the flex form instance we use to configure the flex form for this plugin
     * If this is empty we don't have a flex form for this plugin
     *
     * @var Flex
     */
    protected $flexForm;
    
    /**
     * Returns true if this plugin has a flex form configuration
     *
     * @return bool
     */
    public function hasFlexForm(): bool
    {
        return ! empty($this->flexForm);
    }
    
    /**
     * Returns the flex form structure object for this plugin.
     * You have to call this method at least once to register a flex form file for an element
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Flex
     */
    public function getFlexForm(): Flex
    {
        // Return existing instance
        if (! empty($this->flexForm)) {
            return $this->flexForm;
        }
        
        $this->flexForm = $this->getTypoContext()->di()->getService(Factory::class)->create();
        
        // Try to load the default definition
        try {
            $defaultDefinitionFile = 'file:' . Inflector::toCamelCase($this->pluginName) . '.xml';
            $this->flexForm->loadDefinition($defaultDefinitionFile);
        } catch (MissingFlexFormFileException $e) {
        }
        
        // Done
        return $this->flexForm;
    }
    
    /**
     * @inheritDoc
     */
    protected function getDataHookTableFieldConstraints(): array
    {
        return ['CType' => 'list', 'list_type' => $this->getSignature()];
    }
}
