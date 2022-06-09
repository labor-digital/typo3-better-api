<?php
/*
 * Copyright 2022 LABOR.digital
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
 * Last modified: 2022.06.08 at 14:02
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\TypoScript\Interop;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfig\ExtConfigException;
use LaborDigital\T3ba\ExtConfigHandler\TypoScript\TypoScriptConfigurator;
use TYPO3\CMS\Core\SingletonInterface;

class TypoScriptConfigInteropLayer implements SingletonInterface
{
    /**
     * True if the registration of new configurators has been locked.
     * Means the configurators have been applied and will never be able to be configured.
     *
     * @var bool
     */
    protected $locked = false;
    
    /**
     * The list of configured configurators to be executed when apply is called
     *
     * @var \Closure[]
     */
    protected $configurators = [];
    
    /**
     * The list of namespaces registered for the configurators
     *
     * @var array
     */
    protected $namespaces = [];
    
    /**
     * Registers a new configurator instance. A configurator is a closure that will receive the
     * {@link \LaborDigital\T3ba\ExtConfigHandler\TypoScript\TypoScriptConfigurator}
     * as it's sole parameter. The configurator will be called BEFORE the normal typo script configurations have been applied.
     * This allows other configurators to append typo script through a clean api.
     *
     * @param   \Closure     $configurator
     * @param   string|null  $namespace  Optional namespace to use when applying the config
     *
     * @return void
     * @throws \LaborDigital\T3ba\ExtConfig\ExtConfigException if the configurators have been applied and the class is now locked
     */
    public function registerConfiguration(\Closure $configurator, ?string $namespace = null): void
    {
        if ($this->locked) {
            throw new ExtConfigException(
                'Sorry, you can\'t register a configurator now, because it would never be applied to the typo script configurator;' .
                'try to execute the method at an earlier stage!');
        }
        
        $this->configurators[] = $configurator;
        $this->namespaces[] = $namespace;
    }
    
    /**
     * Returns the list of all registered configurators
     *
     * @return \Closure[]
     */
    public function getConfigurations(): array
    {
        return $this->configurators;
    }
    
    /**
     * Applies the currently registered configurator instances to the provided typo script configurator
     *
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext                          $context
     * @param   \LaborDigital\T3ba\ExtConfigHandler\TypoScript\TypoScriptConfigurator  $configurator
     *
     * @return $this
     */
    public function apply(ExtConfigContext $context, TypoScriptConfigurator $configurator): self
    {
        foreach ($this->configurators as $i => $_configurator) {
            $namespace = $this->namespaces[$i] ?? null;
            if ($namespace) {
                $context->runWithNamespace($namespace, function () use ($_configurator, $configurator) {
                    $_configurator($configurator);
                });
            } else {
                $_configurator($configurator);
            }
        }
        
        return $this;
    }
    
    /**
     * Locks the registration of new configurations in the future
     *
     * @return $this
     */
    public function lock(): self
    {
        $this->locked = true;
        
        return $this;
    }
    
    /**
     * Returns true if the adding of configurations has been disabled
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }
}