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
 * Last modified: 2021.05.16 at 23:29
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\TypoContext;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\ExtConfig\ExtConfigService;
use TYPO3\CMS\Core\Core\Environment;

class FacetProvider implements PublicServiceInterface
{
    use ContainerAwareTrait;
    
    /**
     * The list of facet classes by their short name
     *
     * @var array
     */
    protected $facetClasses;
    
    /**
     * @var \LaborDigital\T3ba\ExtConfig\ExtConfigService
     */
    protected $extConfigService;
    
    public function __construct(array $facetClasses, ExtConfigService $extConfigService)
    {
        $this->facetClasses = $facetClasses;
        $this->extConfigService = $extConfigService;
    }
    
    /**
     * Returns all registered facet classes by their short name
     * It will also make sure that the autocomplete helper will be generated in a dev environment
     *
     *
     * @return array
     */
    public function getAll(): array
    {
        if ($this->isDev()) {
            $this->makeAutocompleteHelper();
        }
        
        return $this->facetClasses;
    }
    
    /**
     * Returns true if the current instance is running in development context
     *
     * @return bool
     */
    protected function isDev(): bool
    {
        return Environment::getContext()->isDevelopment();
    }
    
    /**
     * Loads and triggers the autocomplete helper generator for development environments
     *
     * @param   array  $presets
     */
    protected function makeAutocompleteHelper(): void
    {
        $generator = $this->makeInstance(
            AutocompleteGenerator::class,
            [$this->extConfigService->getFsMount()]
        );
        
        $generator->generate($this->facetClasses);
    }
}