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
 * Last modified: 2021.04.29 at 22:19
 */

namespace PHPSTORM_META {
    
    use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
    use LaborDigital\T3BA\Core\Di\StaticContainerAwareTrait;
    use LaborDigital\T3BA\Tool\TypoContext\Facet\DependencyInjectionFacet;
    use Neunerlei\EventBus\EventBusInterface;
    use Psr\Container\ContainerInterface;
    use Psr\EventDispatcher\EventDispatcherInterface;
    use TYPO3\CMS\Core\Utility\GeneralUtility;
    use TYPO3\CMS\Extbase\Object\ObjectManager;
    use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
    
    // TYPO3 Core
    override(ObjectManager::get(0), type(0));
    override(ObjectManagerInterface::get(0), type(0));
    override(ContainerInterface::get(0), type(0));
    override(GeneralUtility::makeInstance(0), type(0));
    
    // Better API
    override(ContainerAwareTrait::getService(0), type(0));
    override(StaticContainerAwareTrait::getService(0), type(0));
    override(DependencyInjectionFacet::getService(0), type(0));
    
    override(ContainerAwareTrait::makeInstance(0), type(0));
    override(StaticContainerAwareTrait::makeInstance(0), type(0));
    override(DependencyInjectionFacet::makeInstance(0), type(0));
    
    override(TypoEventBus::dispatch(0), type(0));
    override(EventBusInterface::dispatch(0), type(0));
    override(EventDispatcherInterface::dispatch(0), type(0));
}
