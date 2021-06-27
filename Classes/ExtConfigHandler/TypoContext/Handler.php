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


namespace LaborDigital\T3ba\ExtConfigHandler\TypoContext;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigHandler;
use LaborDigital\T3ba\ExtConfig\ExtConfigException;
use LaborDigital\T3ba\ExtConfig\Interfaces\DiBuildTimeHandlerInterface;
use LaborDigital\T3ba\Tool\TypoContext\FacetInterface;
use LaborDigital\T3ba\Tool\TypoContext\FacetProvider;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Handler extends AbstractExtConfigHandler implements DiBuildTimeHandlerInterface, NoDiInterface
{
    /**
     * A list of collected facets by their short name
     *
     * @var array
     */
    protected $facets = [];
    
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $configurator->registerInterface(FacetInterface::class);
        $configurator->registerLocation('Classes/TypoContext');
    }
    
    /**
     * @inheritDoc
     */
    public function prepare(): void { }
    
    /**
     * @inheritDoc
     */
    public function handle(string $class): void
    {
        $identifier = call_user_func([$class, 'getIdentifier']);
        
        if (empty($identifier)) {
            throw new ExtConfigException('The returned identifier of facet class: ' . $class . ' is empty!');
        }
        
        if (preg_match('~^(?=_*[A-Za-z]+)[A-Za-z0-9_]+$~', $identifier) === false) {
            throw new ExtConfigException('The given facet identifier: "' . $identifier . '" contains invalid chars. Only alphanumeric characters and _ are allowed!');
        }
        
        if (isset($this->facets[$identifier]) && $this->facets[$identifier] !== $class) {
            throw new ExtConfigException('A facet overlap occurred. The identifier: "' . $identifier . '" was registered for multiple classes: ' . $class . ', and ' . $this->facets[$identifier]);
        }
        
        $this->facets[$identifier] = $class;
    }
    
    /**
     * @inheritDoc
     */
    public function finish(): void
    {
        /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder */
        $containerBuilder = $this->getInstance(ContainerBuilder::class);
        $containerBuilder->getDefinition(FacetProvider::class)
                         ->setArgument(0, $this->facets);
    }
    
}