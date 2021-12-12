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
 * Last modified: 2021.12.12 at 23:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\ConfigurationModule;


use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractGroupExtConfigHandler;
use LaborDigital\T3ba\ExtConfig\Interfaces\DiBuildTimeHandlerInterface;
use LaborDigital\T3ba\ExtConfigHandler\Di\BuildTimeHandler;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TYPO3\CMS\Lowlevel\ConfigurationModuleProvider\ProviderInterface;

class Handler extends AbstractGroupExtConfigHandler implements DiBuildTimeHandlerInterface
{
    protected ProviderConfigurator $configurator;
    
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        if (! interface_exists(ProviderInterface::class)) {
            return;
        }
        
        $configurator->registerLocation('Classes/ConfigurationModule');
        $configurator->registerInterface(ConfigureConfigurationModuleProviderInterface::class);
        $configurator->executeThisHandlerAfter(BuildTimeHandler::class);
    }
    
    /**
     * @inheritDoc
     */
    protected function getGroupKeyOfClass(string $class): string
    {
        /** @var ConfigureConfigurationModuleProviderInterface $class */
        return $class::getProviderIdentifier();
    }
    
    /**
     * @inheritDoc
     */
    public function prepareHandler(): void { }
    
    /**
     * @inheritDoc
     */
    public function finishHandler(): void { }
    
    /**
     * @inheritDoc
     */
    public function prepareGroup(string $groupKey, array $groupClasses): void
    {
        $this->configurator = $this->getInstanceWithoutDi(ProviderConfigurator::class, [$groupKey]);
        foreach ($groupClasses as $className) {
            if (in_array(ProviderInterface::class, class_implements($className), true)) {
                $this->configurator->setClassName($className);
                break;
            }
        }
    }
    
    /**
     * @inheritDoc
     */
    public function handleGroupItem(string $class): void
    {
        /** @var ConfigureConfigurationModuleProviderInterface $class */
        $class::configureProvider($this->configurator, $this->context);
    }
    
    /**
     * @inheritDoc
     */
    public function finishGroup(string $groupKey, array $groupClasses): void
    {
        $this->configurator->finish($this->getInstance(ContainerBuilder::class));
    }
    
}