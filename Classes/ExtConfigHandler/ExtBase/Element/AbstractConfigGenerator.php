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
 * Last modified: 2021.06.11 at 19:41
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator;

abstract class AbstractConfigGenerator
{
    use ContainerAwareTrait;
    
    /**
     * @var \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\SharedConfig
     */
    protected $config;
    
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