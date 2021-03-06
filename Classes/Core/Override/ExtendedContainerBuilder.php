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


namespace LaborDigital\T3ba\Core\Override;


use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Event\CreateDiContainerEvent;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\DependencyInjection\T3BaCopyContainerBuilder;
use TYPO3\CMS\Core\Package\PackageManager;

class ExtendedContainerBuilder extends T3BaCopyContainerBuilder
{
    /**
     * @inheritDoc
     */
    public function __construct(array $earlyInstances)
    {
        parent::__construct($earlyInstances);
        $this->defaultServices += [static::class => $this];
    }
    
    /**
     * @inheritDoc
     */
    public function createDependencyInjectionContainer(
        PackageManager $packageManager,
        FrontendInterface $cache,
        bool $failsafe = false
    ): ContainerInterface
    {
        return TypoEventBus::getInstance()->dispatch(
            new CreateDiContainerEvent(
                $failsafe,
                $packageManager,
                parent::createDependencyInjectionContainer($packageManager, $cache, $failsafe)
            )
        )->getContainer();
    }
    
    
}
