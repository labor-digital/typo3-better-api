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
 * Last modified: 2020.08.24 at 20:15
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig;


use LaborDigital\T3BA\Core\Event\ConfigLoaderFilterEvent;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\Exception\NotImplementedException;
use LaborDigital\T3BA\Core\TempFs\TempFs;
use Neunerlei\Configuration\Loader\Loader;
use Neunerlei\PathUtil\Path;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtConfigService implements SingletonInterface
{
    /**
     * The list of default handler locations to traverse.
     * This is a public "api" and can be extended if you need to
     */
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
     * @var \LaborDigital\T3BA\Core\TempFs\TempFs
     */
    protected $fs;

    /**
     * The list of collected root locations
     *
     * @var array
     */
    protected $rootLocations;

    /**
     * ExtConfigService constructor.
     *
     * @param   \TYPO3\CMS\Core\Package\PackageManager         $packageManager
     * @param   \LaborDigital\T3BA\Core\EventBus\TypoEventBus  $eventBus
     * @param   \LaborDigital\T3BA\Core\TempFs\TempFs          $fs
     */
    public function __construct(PackageManager $packageManager, TypoEventBus $eventBus, TempFs $fs)
    {
        $this->packageManager = $packageManager;
        $this->eventBus       = $eventBus;
        $this->fs             = $fs;
    }

    /**
     * Returns the local storage filesystem instance
     *
     * @return \LaborDigital\T3BA\Core\TempFs\TempFs
     */
    public function getFs(): TempFs
    {
        return $this->fs;
    }

    /**
     * Creates the new, preconfigured instance of an ext config loader
     *
     * @param   string  $type
     *
     * @return \Neunerlei\Configuration\Loader\Loader
     */
    public function makeLoader(string $type): Loader
    {
        $appContext = Environment::getContext();
        $loader     = GeneralUtility::makeInstance(Loader::class, $type, (string)$appContext);
        $loader->setConfigContextClass(ExtConfigContext::class);
        $loader->setCache($this->fs->getCache());
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
                    // @todo implement this
                    throw new NotImplementedException('This is not yet implemented');
                    dbge($class, $package);
                },
            ];
        }

        return $this->rootLocations = $rootLocations;
    }

    /**
     * Returns the namespace lists for the auto loader and the ext-key namespace map
     *
     * @return array[]
     */
    protected function getNamespaceMaps(): array
    {
        $cache    = $this->fs->getCache();
        $cacheKey = 'namespaceMaps-' . $this->packageManager->getCacheIdentifier();

        if ($cache->has($cacheKey)) {
            return $cache->get($cacheKey);
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
        $cache->set($cacheKey, $maps);

        return $maps;
    }
}
