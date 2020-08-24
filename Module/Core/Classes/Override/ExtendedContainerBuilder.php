<?php
/*
 * Copyright 2020 LABOR.digital
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
 * Last modified: 2020.08.22 at 22:32
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\Override;


use LaborDigital\T3BA\Core\DependencyInjection\FailsafeDelegateContainer;
use LaborDigital\T3BA\Core\Event\DiContainerFilterEvent;
use LaborDigital\T3BA\Core\Event\Internal\InternalCreateDependencyInjectionContainerEvent;
use LaborDigital\T3BA\Core\Event\Internal\InternalDiContainerFilterEvent;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\Util\FailsafeWrapper;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\DependencyInjection\BetterApiClassOverrideCopy__ContainerBuilder;
use TYPO3\CMS\Core\Package\PackageManager;

class ExtendedContainerBuilder extends BetterApiClassOverrideCopy__ContainerBuilder
{
    /**
     * @inheritDoc
     */
    public function __construct(array $earlyInstances)
    {
        parent::__construct($earlyInstances);
        $this->defaultServices += [get_called_class() => $this];
    }

    /**
     * @inheritDoc
     */
    public function createDependencyInjectionContainer(
        PackageManager $packageManager,
        FrontendInterface $cache,
        bool $failsafe = false
    ): ContainerInterface {
        TypoEventBus::getInstance()->dispatch(
            $e = new InternalCreateDependencyInjectionContainerEvent(
                $failsafe,
                func_get_args(),
                function (...$args) {
                    return parent::createDependencyInjectionContainer(...$args);
                }
            )
        );

        return $e->getContainer();


        return FailsafeWrapper::handleEither(function (
            PackageManager $packageManager,
            FrontendInterface $cache,
            bool $failsafe = false
        ) {
            /** @var \Symfony\Component\DependencyInjection\Container $container */
            $container = parent::createDependencyInjectionContainer($packageManager, $cache, false);
            $eventBus  = TypoEventBus::getInstance();
            $eventBus->dispatch(new InternalDiContainerFilterEvent($container));
            $eventBus->dispatch(new DiContainerFilterEvent($container));

            if ($failsafe) {
                return new FailsafeDelegateContainer(
                    parent::createDependencyInjectionContainer($packageManager, $cache, true),
                    $container
                );
            }

            return $container;
        }, function (
            PackageManager $packageManager,
            FrontendInterface $cache
        ) {
            dbge('FOO', func_get_args());

            return parent::createDependencyInjectionContainer($packageManager, $cache, true);

        }, func_get_args(), func_get_args());
    }


}
