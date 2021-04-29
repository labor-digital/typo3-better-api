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


namespace LaborDigital\T3BA\ExtConfigHandler\Fluid;


use LaborDigital\T3BA\ExtConfig\Abstracts\AbstractExtConfigConfigurator;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\Inflection\Inflector;

class FluidConfigurator extends AbstractExtConfigConfigurator
{
    
    /**
     * The list of registered view helper namespaces
     *
     * @var array
     */
    protected $viewHelpers = [];
    
    /**
     * Globally registers the extension's view helpers when $key and $namespace are empty.
     * The default key is the CamelCase of your extension key, and the namespace the CamelCase of
     * Vendor\ExtensionKey\ViewHelpers.
     *
     * You can also specify namespaces for other viewhelpers
     *
     * @param   string|NULL  $key        The key to use as prefix for the namespaced viewhelpers
     * @param   string|NULL  $namespace  The namespace of the viewhelpers
     *
     * @return $this
     */
    public function registerViewHelpers(?string $key = null, ?string $namespace = null): self
    {
        if (empty($key)) {
            $key = Inflector::toCamelCase($this->context->getExtKey());
        }
        
        if (empty($namespace)) {
            $namespace = '';
            if (! empty($this->context->getVendor())) {
                $namespace = Inflector::toCamelCase($this->context->getVendor()) . '\\';
            }
            $namespace .= Inflector::toCamelCase($this->context->getExtKey());
            $namespace .= '\\ViewHelpers';
        }
        
        $this->viewHelpers[$this->context->replaceMarkers($key)][] = $this->context->replaceMarkers($namespace);
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function finish(ConfigState $state): void
    {
        $state->mergeIntoArray('TYPO3_CONF_VARS.SYS.fluid.namespaces', $this->viewHelpers);
    }
}
