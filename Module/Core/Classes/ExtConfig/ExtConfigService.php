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
 * Last modified: 2020.08.23 at 15:43
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\ExtConfig;


use LaborDigital\T3BA\Core\Event\ConfigLoaderFilterEvent;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use Neunerlei\Configuration\Loader\Loader;
use Neunerlei\PathUtil\Path;
use Psr\SimpleCache\CacheInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtConfigService implements SingletonInterface
{
    public static $handlerLocations
        = [
            'Classes/ExtConfigHandler/**',
        ];

    /**
     * @var \TYPO3\CMS\Core\Package\PackageManager
     */
    protected $packageManager;

    /**
     * @var \LaborDigital\T3BA\Core\EventBus\TypoEventBus
     */
    protected $eventBus;

    /**
     * @var \LaborDigital\T3BA\Core\TempFs\TempFsCache
     */
    protected $cache;

    protected $rootLocations;


    /**
     * ExtConfigService constructor.
     *
     * @param   \TYPO3\CMS\Core\Package\PackageManager         $packageManager
     * @param   \LaborDigital\T3BA\Core\EventBus\TypoEventBus  $eventBus
     * @param   \Psr\SimpleCache\CacheInterface                $cache
     */
    public function __construct(PackageManager $packageManager, TypoEventBus $eventBus, CacheInterface $cache)
    {
        $this->packageManager = $packageManager;
        $this->eventBus       = $eventBus;
        $this->cache          = $cache;
    }

    public function makeLoader(string $type): Loader
    {
        $appContext = Environment::getContext();
        $loader     = GeneralUtility::makeInstance(Loader::class, $type, (string)$appContext);
        $loader->setConfigContextClass(ExtConfigContext::class);
        $loader->setCache($this->cache);
        foreach ($this->getRootLocations() as $rootLocation) {
            $loader->registerRootLocation(...$rootLocation);
        }
        foreach (static::$handlerLocations as $handlerLocation) {
            $loader->registerHandlerLocation($handlerLocation);
        }

        $this->eventBus->dispatch(($e = new ConfigLoaderFilterEvent($loader)));

        return $e->getLoader();
    }

    /**
     * Returns a list of all namespaces for each activated ext key
     *
     * @return array
     */
    public function getExtKeyNamespaceMap(): array
    {
        return $this->getNamespaceMaps()['extKeyNamespace'];
    }

    /**
     * Returns the list of configuration php namespaces and the matching file paths for all active extensions
     *
     * @return array
     */
    public function getAutoloaderMap(): array
    {
        return $this->getNamespaceMaps()['autoload'];
    }

    /**
     * Finds the list of all possible root locations and returns them
     * in form of an array, containing arrays with both the path and namespace generator
     *
     * @return array
     */
    protected function getRootLocations(): array
    {
        if (is_array($this->rootLocations)) {
            return $this->rootLocations;
        }

        $rootLocations = [];

        // Add better api module locations
        $betterApi       = $this->packageManager->getPackage('T3BA');
        $rootLocations[] = [
            $betterApi->getPackagePath() . 'Module/*/',
            'LaborDigital.T3BA',
        ];

        // Add extension locations
        foreach ($this->packageManager->getActivePackages() as $package) {
            $rootLocations[] = [
                $package->getPackagePath(),
                function ($file, string $class) use ($package) {
                    dbge($class, $package);
                },
            ];
        }

        return $this->rootLocations = $rootLocations;
    }

    protected function getNamespaceMaps(): array
    {
        if ($this->cache->has('NamespaceMaps')) {
            return $this->cache->get('NamespaceMaps');
        }

        $autoloadMap        = [];
        $extKeyNamespaceMap = [];
        foreach ($this->packageManager->getActivePackages() as $package) {
            $autoload = $package->getValueFromComposerManifest('autoload');
            if (! is_object($autoload)) {
                continue;
            }
            if (! is_object($autoload->{'psr-4'})) {
                continue;
            }
            foreach ((array)$autoload->{'psr-4'} as $namespace => $directory) {
                $directory = trim($directory, '/.');
                if ($directory === 'Classes' || str_ends_with($directory, '/Classes')) {
                    $extKeyNamespaceMap[$package->getPackageKey()][$namespace] = $directory;
                    $potentialConfigDir                                        = Path::join($package->getPackagePath(),
                        dirname($directory), 'Configuration');
                    if (is_dir($potentialConfigDir)) {
                        $autoloadMap[$namespace . 'Configuration\\'] = $potentialConfigDir;
                    }
                }
            }
        }

        $maps = ['autoload' => $autoloadMap, 'extKeyNamespace' => $extKeyNamespaceMap];
        $this->cache->set('NamespaceMaps', $maps);

        return $maps;
    }
}
