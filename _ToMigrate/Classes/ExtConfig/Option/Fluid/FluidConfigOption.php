<?php
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
 * Last modified: 2020.03.18 at 19:41
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\Fluid;

use LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class FluidConfigOption
 *
 * Can be used to configure the FLUID template engine
 *
 * @package LaborDigital\Typo3BetterApi\ExtConfig\Option\Fluid
 */
class FluidConfigOption extends AbstractExtConfigOption implements SingletonInterface
{
    
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
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Fluid\FluidConfigOption
     */
    public function registerViewHelpers(string $key = null, string $namespace = null): FluidConfigOption
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
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'][$this->replaceMarkers($key)]
            = [$this->replaceMarkers($namespace)];
        
        return $this;
    }
}
