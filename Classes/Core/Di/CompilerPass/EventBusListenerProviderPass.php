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


namespace LaborDigital\T3ba\Core\Di\CompilerPass;


use LaborDigital\T3ba\Core\Di\ServiceFactory;
use LaborDigital\T3ba\Core\EventBus\TypoListenerProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;

class EventBusListenerProviderPass implements CompilerPassInterface
{
    
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        // Override the listener provider class
        $innerId = $container->findDefinition(ListenerProvider::class)->innerServiceId;
        $listenerProvider = $container->getDefinition($innerId);
        $listenerProvider->setClass(TypoListenerProvider::class);
        $listenerProvider->setFactory([ServiceFactory::class, 'getListenerProvider']);
        $listenerProvider->setPublic(true);
    }
    
}
