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
 * Last modified: 2021.11.23 at 10:49
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Event\Configuration;

/**
 * Used to gather expression language providers for the typo script expression language
 *
 * @see \TYPO3\CMS\Core\ExpressionLanguage\ProviderInterface
 */
class ExpressionLanguageRegistrationEvent
{
    /**
     * The list of registered expression language provider classes
     *
     * @var array
     */
    protected $providers = [];
    
    /**
     * Returns the list of registered provider classes
     *
     * @return array
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
    
    /**
     * Resets the list of all registered provider classes to the given value
     *
     * @param   array  $providers
     *
     * @return $this
     */
    public function setProviders(array $providers, string $context = 'typoscript'): self
    {
        $this->providers[$context] = $providers;
        
        return $this;
    }
    
    /**
     * Adds a single provider class to the list of providers
     *
     * @param   string  $providerClass
     *
     * @return $this
     * @see \TYPO3\CMS\Core\ExpressionLanguage\ProviderInterface
     */
    public function addProvider(string $providerClass, string $context = 'typoscript'): self
    {
        $this->providers[$context] = array_unique(array_merge($this->providers[$context] ?? [], [$providerClass]));
        
        return $this;
    }
}