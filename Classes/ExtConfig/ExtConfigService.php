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
 * Last modified: 2020.08.24 at 20:17
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig;


use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\VarFs\Mount;
use LaborDigital\T3BA\Core\VarFs\VarFs;
use LaborDigital\T3BA\Event\ConfigLoaderFilterEvent;
use Neunerlei\Configuration\Loader\Loader;
use Neunerlei\PathUtil\Path;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtConfigService implements SingletonInterface
{
    public const MAIN_LOADER_KEY         = 'ExtConfigMain';
    public const EVENT_BUS_LOADER_KEY    = 'EventBus';
    public const DI_LOADER_KEY           = 'Di';
    public const TCA_LOADER_KEY          = 'Tca';
    public const TCA_OVERRIDE_LOADER_KEY = 'TcaOverride';

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
     * @var \LaborDigital\T3BA\Core\VarFs\VarFs
     */
    protected $fs;

    /**
     * The list of collected root locations
     *
     * @var array
     */
    protected $rootLocations;

    /**
     * An internal cache between class names and their matching namespaces
     *
     * @var array
     */
    protected $classNamespaceCache = [];

    /**
     * ExtConfigService constructor.
     *
     * @param   \TYPO3\CMS\Core\Package\PackageManager         $packageManager
     * @param   \LaborDigital\T3BA\Core\EventBus\TypoEventBus  $eventBus
     * @param   \LaborDigital\T3BA\Core\VarFs\VarFs            $fs
     */
    public function __construct(PackageManager $packageManager, TypoEventBus $eventBus, VarFs $fs)
    {
        $this->packageManager = $packageManager;
        $this->eventBus       = $eventBus;
        $this->fs             = $fs;
    }

    /**
     * Returns the local storage filesystem instance
     *
     * @return \LaborDigital\T3BA\Core\VarFs\VarFs
     */
    public function getFs(): VarFs
    {
        return $this->fs;
    }

    /**
     * Returns the fs mount were ext config related data should be stored
     *
     * @return \LaborDigital\T3BA\Core\VarFs\Mount
     */
    public function getFsMount(): Mount
    {
        return $this->fs->getMount('ExtConfig');
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
        $loader->setEventDispatcher($this->eventBus);
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
        foreach ($this->packageManager->getActivePackages() as $package) {
            $rootLocations[] = [
                $package->getPackagePath(),
                function ($file, string $class) use ($package) {
                    if (isset($this->classNamespaceCache[$class])) {
                        return $this->classNamespaceCache[$class];
                    }

                    $classParts = array_filter(explode('\\', $class));
                    if (count($classParts) === 1) {
                        $namespace = $package->getPackageKey();
                    } else {
                        $namespace = reset($classParts) . '.' . $package->getPackageKey();
                    }

                    return $this->classNamespaceCache[$class] = $namespace;
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
