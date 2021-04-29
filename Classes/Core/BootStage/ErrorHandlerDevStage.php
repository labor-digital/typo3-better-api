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
use LaborDigital\T3BA\Event\Core\ErrorFilterEvent;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\Exception\ConfigClassNotAutoloadableException;
use Neunerlei\FileSystem\Fs;
use Neunerlei\PathUtil\Path;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Throwable;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Service\ClearCacheService;

class ErrorHandlerDevStage implements BootStageInterface
{
    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @inheritDoc
     */
    public function prepare(TypoEventBus $eventBus, Kernel $kernel): void
    {
        $this->kernel = $kernel;
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
        if ($error instanceof ConfigClassNotAutoloadableException
            || $error instanceof \LaborDigital\T3BA\Tool\Cache\InvalidArgumentException
            || $error instanceof InvalidArgumentException
            || ($error instanceof \InvalidArgumentException
                && preg_match('~Event listener ".*?" is not callable~i', $error->getMessage()))
            || ($error instanceof Error && preg_match('~class \'.*?\' not found~i', $error->getMessage()))
            || ($error instanceof ArgumentCountError
                && in_array(Container::class, Arrays::getList(array_slice($error->getTrace(), 0, 6), 'class'), true))
        ) {
            $this->clearAllCaches();
        }
    }

    /**
     * Internal helper to flush all TYPO3 caches.
     * This will reset the system to a blank slate if we encounter issues with the DI container
     */
    protected function clearAllCaches(): void
    {
        $kernel = $this->kernel;
        register_shutdown_function(static function () use ($kernel) {
            $state = true;

            // Try to flush the extbase caches
            try {
                $coreCacheService = GeneralUtility::makeInstance(ClearCacheService::class);
                $coreCacheService->clearAll();
            } catch (Throwable $e) {
                $state = false;
            }

            // Try to flush all files
            try {
                $cachePath = Path::join(Environment::getVarPath(), 'cache');
                Fs::flushDirectory($cachePath);
            } catch (Throwable $e) {
                $state = false;
            }

            // Try to flush the di cache hard
            if (! $state) {
                try {
                    Bootstrap::createCache('di')->getBackend()->forceFlush();
                } catch (Throwable $e) {
                }
            }

            // Flush our internal file system
            try {
                $kernel->getFs()->flush();
            } catch (Throwable $e) {
            }
        });
    }

}
