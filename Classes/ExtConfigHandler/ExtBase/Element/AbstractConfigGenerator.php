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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator;
use LaborDigital\T3ba\ExtConfigHandler\TypoScript\Interop\TypoScriptConfigInteropLayer;
use LaborDigital\T3ba\ExtConfigHandler\TypoScript\TypoScriptConfigurator;

abstract class AbstractConfigGenerator
{
    use ContainerAwareTrait;
    
    /**
     * @var \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\SharedConfig
     */
    protected $config;
    
    /**
     * @var \LaborDigital\T3ba\ExtConfigHandler\TypoScript\Interop\TypoScriptConfigInteropLayer
     */
    protected $tsInterop;
    
    public function injectTsInterop(TypoScriptConfigInteropLayer $tsInterop): void
    {
        $this->tsInterop = $tsInterop;
    }
    
    /**
     * Injects the shared config object on which the data is stored
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\SharedConfig  $config
     */
    public function setConfig(SharedConfig $config): void
    {
        $this->config = $config;
    }
    
    /**
     * Automatically iterates all variants and calls generateForVariant() for each of them
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext                                   $context
     */
    public function generate(AbstractElementConfigurator $configurator, ExtConfigContext $context): void
    {
        foreach (array_merge([$configurator], $configurator->getVariants()) as $variantName => $variant) {
            $this->generateForVariant($variant, $context, $variantName === 0 ? null : $variantName);
        }
    }
    
    /**
     * Internal helper to register the typo script and ts config
     *
     * @param   array   $typoScript
     * @param   array   $tsConfig
     * @param   string  $namespace
     *
     * @return void
     */
    protected function registerTypoScript(array $typoScript, array $tsConfig, string $namespace): void
    {
        if (! $this->tsInterop) {
            return;
        }
        
        $this->tsInterop->registerConfiguration(function (TypoScriptConfigurator $configurator) use ($typoScript, $tsConfig) {
            if (! empty($typoScript)) {
                $configurator->registerDynamicContent('extBase.setup', implode(PHP_EOL, array_filter($typoScript)));
            }
            
            if (! empty($tsConfig)) {
                $configurator->registerPageTsConfig(implode(PHP_EOL, array_filter($tsConfig)));
            }
        }, $namespace);
    }
    
    /**
     * Executed once for every variant registered on the element
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext                                   $context
     * @param   string|null                                                                     $variantName
     */
    abstract public function generateForVariant(
        AbstractElementConfigurator $configurator,
        ExtConfigContext $context,
        ?string $variantName
    ): void;
}