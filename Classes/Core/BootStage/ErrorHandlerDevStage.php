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
 * Last modified: 2020.08.24 at 08:23
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\BootStage;


use ArgumentCountError;
use Error;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\Kernel;
use LaborDigital\T3BA\Core\TempFs\TempFs;
use LaborDigital\T3BA\Event\Core\ErrorFilterEvent;
use Neunerlei\Configuration\Exception\ConfigClassNotAutoloadableException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;

class ErrorHandlerDevStage implements BootStageInterface
{
    /**
     * @inheritDoc
     */
    public function prepare(TypoEventBus $eventBus, Kernel $kernel): void
    {
        $eventBus->addListener(ErrorFilterEvent::class, [$this, 'onError']);
    }

    /**
     * Special development only error handling helpers.
     * This clears your container cache if it failed to resolve something, which allows you easier debugging.
     *
     * @param   \LaborDigital\T3BA\Event\Core\ErrorFilterEvent  $event
     */
    public function onError(ErrorFilterEvent $event): void
    {
        if (! Environment::getContext()->isDevelopment()) {
            return;
        }

        // Flush the DI cache if a service was not defined, or changed
        $error = $event->getError();
        if ($error instanceof ArgumentCountError
            || $error instanceof ConfigClassNotAutoloadableException
            || $error instanceof InvalidArgumentException
            || ($error instanceof \InvalidArgumentException
                && preg_match('~Event listener ".*?" is not callable~i', $error->getMessage()))
            || ($error instanceof Error && preg_match('~class \'.*?\' not found~i', $error->getMessage()))
        ) {
            Bootstrap::createCache('di')->getBackend()->forceFlush();
            TempFs::makeInstance('')->flush();
        }
    }

}
