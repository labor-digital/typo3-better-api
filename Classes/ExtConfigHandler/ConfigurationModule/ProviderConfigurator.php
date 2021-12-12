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
 * Last modified: 2021.12.12 at 23:07
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\ConfigurationModule;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\ExtConfigException;
use LaborDigital\T3ba\ExtConfig\Interfaces\ExtConfigContextAwareInterface;
use LaborDigital\T3ba\ExtConfig\Traits\ExtConfigContextAwareTrait;
use Neunerlei\Inflection\Inflector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use TYPO3\CMS\Lowlevel\ConfigurationModuleProvider\ProviderInterface;

class ProviderConfigurator implements NoDiInterface, ExtConfigContextAwareInterface
{
    use ExtConfigContextAwareTrait;
    
    /**
     * A unique identifier for the configuration provider
     *
     * @var string
     */
    protected string $identifier;
    
    /**
     * A speaking label for the configuration provider
     *
     * @var string
     */
    protected string $label;
    
    /**
     * The name of the provider class to use
     *
     * @var string
     */
    protected string $className;
    
    /**
     * A list of other provider identifiers that should be rendered after the current provider
     *
     * @var array
     */
    protected array $before = [];
    
    /**
     * A list of other provider identifiers that should be rendered before the current provider
     *
     * @var array
     */
    protected array $after = [];
    
    /**
     * If this is set to true this provider will be disabled
     *
     * @var bool
     */
    protected bool $disabled = false;
    
    /**
     * Contains custom options to pass to the provider
     *
     * @var array
     */
    protected array $custom = [];
    
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
        $this->label = Inflector::toHuman($identifier);
    }
    
    /**
     * Returns the unique identifier for the configuration provider
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
    
    /**
     * Returns the speaking label for the configuration provider
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }
    
    /**
     * Sets the speaking label for the configuration provider
     *
     * @param   string  $label
     *
     * @return ProviderConfigurator
     */
    public function setLabel(string $label): ProviderConfigurator
    {
        $this->label = $this->context->replaceMarkers($label);
        
        return $this;
    }
    
    /**
     * Returns the name of the provider class to use
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
    
    /**
     * Allows you to change the provider class to another implementation.
     *
     * @param   string  $className
     *
     * @return ProviderConfigurator
     */
    public function setClassName(string $className): ProviderConfigurator
    {
        if (! in_array(ProviderInterface::class, class_implements($className), true)) {
            throw new \InvalidArgumentException(
                'Invalid provider class given. The class: "' . $className .
                '" must implement the required interface: "' . ProviderInterface::class . '"');
        }
        
        $this->className = $className;
        
        return $this;
    }
    
    /**
     * Returns the list of other provider identifiers that should be rendered after the current provider
     *
     * @return array
     */
    public function getBefore(): array
    {
        return $this->before;
    }
    
    /**
     * Sets the list of other provider identifiers that should be rendered after the current provider
     *
     * @param   array  $before
     *
     * @return ProviderConfigurator
     */
    public function setBefore(array $before): ProviderConfigurator
    {
        $this->before = $this->context->replaceMarkers($before);
        
        return $this;
    }
    
    /**
     * Returns the list of other provider identifiers that should be rendered before the current provider
     *
     * @return array
     */
    public function getAfter(): array
    {
        return $this->after;
    }
    
    /**
     * Sets the list of other provider identifiers that should be rendered before the current provider
     *
     * @param   array  $after
     *
     * @return ProviderConfigurator
     */
    public function setAfter(array $after): ProviderConfigurator
    {
        $this->after = $this->context->replaceMarkers($after);
        
        return $this;
    }
    
    /**
     * Returns true if the provider has been disabled
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }
    
    /**
     * Allows extensions to disable this provider
     *
     * @param   bool  $disabled
     *
     * @return ProviderConfigurator
     */
    public function setDisabled(bool $disabled): ProviderConfigurator
    {
        $this->disabled = $disabled;
        
        return $this;
    }
    
    /**
     * Returns custom options to pass to the provider
     *
     * @return array
     */
    public function getCustom(): array
    {
        return $this->custom;
    }
    
    /**
     * Allows your code to add custom options for the provider
     *
     * @param   array  $custom
     *
     * @return ProviderConfigurator
     */
    public function setCustom(array $custom): ProviderConfigurator
    {
        $this->custom = $this->context->replaceMarkers($custom);
        
        return $this;
    }
    
    /**
     * Finishes the configuration by injecting it into the provided container builder
     *
     * @param   \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator  $containerBuilder
     *
     * @throws \LaborDigital\T3ba\ExtConfig\ExtConfigException
     */
    public function finish(ContainerBuilder $containerBuilder): void
    {
        if (! isset($this->className)) {
            throw new ExtConfigException('Missing class name for configuration provider with id: "' . $this->identifier . '"');
        }
        
        $serviceKey = strtolower($this->context->replaceMarkers(
            '{{extKeyWithVendor}}.configuration.module.provider.' . $this->identifier));
        
        if (! $containerBuilder->has($serviceKey)) {
            $def = $containerBuilder->setDefinition($serviceKey, new Definition($this->className));
            $def->setAutoconfigured(true);
            $def->setAutowired(true);
        } else {
            $def = $containerBuilder->getDefinition($serviceKey);
        }
        
        $def->addTag('lowlevel.configuration.module.provider', array_merge(
            $this->custom,
            [
                'identifier' => $this->identifier,
                'label' => $this->label,
                'before' => implode(',', $this->before),
                'after' => implode(',', $this->after),
            ],
            $this->disabled ? ['disabled' => true] : []
        ));
        
        
        dbg($def);
    }
    
    
}