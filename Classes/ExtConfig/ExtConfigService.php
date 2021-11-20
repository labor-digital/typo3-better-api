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


namespace LaborDigital\T3ba\ExtConfig;


use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Core\VarFs\Mount;
use LaborDigital\T3ba\Core\VarFs\VarFs;
use LaborDigital\T3ba\Event\ConfigLoaderFilterEvent;
use LaborDigital\T3ba\ExtConfig\Loader\DiLoader;
use LaborDigital\T3ba\ExtConfig\Loader\MainLoader;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use Neunerlei\Configuration\Loader\Loader;
use Neunerlei\Configuration\Loader\LoaderContext;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\PathUtil\Path;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtConfigService
{
    public const MAIN_LOADER_KEY = 'Main';
    /** @deprecated will be removed in v11, where siteBased config is loaded along-side the MAIN_LOADER_KEY */
    public const SITE_BASED_LOADER_KEY = 'SiteBased';
    public const EVENT_BUS_LOADER_KEY = 'EventBus';
    public const DI_BUILD_LOADER_KEY = 'DiBuild';
    public const DI_RUN_LOADER_KEY = 'DiRun';
    
    public const PERSISTABLE_STATE_PATH = 't3ba.stateCacheKey';
    
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
     * @var \LaborDigital\T3ba\Core\EventBus\TypoEventBus
     */
    protected $eventBus;
    
    /**
     * @var \LaborDigital\T3ba\Core\VarFs\VarFs
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
     * The list of instantiated loader objects
     *
     * @var array
     */
    protected $loaders = [];
    
    /**
     * The config context we use as a singleton on all loaders
     *
     * @var \LaborDigital\T3ba\ExtConfig\ExtConfigContext
     */
    protected $context;
    
    /**
     * The dependency injection container if we have one
     *
     * @var null|\Psr\Container\ContainerInterface
     */
    protected $container;
    
    /**
     * ExtConfigService constructor.
     *
     * @param   \TYPO3\CMS\Core\Package\PackageManager         $packageManager
     * @param   \LaborDigital\T3ba\Core\EventBus\TypoEventBus  $eventBus
     * @param   \LaborDigital\T3ba\Core\VarFs\VarFs            $fs
     * @param   \Psr\Container\ContainerInterface              $container
     */
    public function __construct(
        PackageManager $packageManager,
        TypoEventBus $eventBus,
        VarFs $fs,
        ContainerInterface $container
    )
    {
        $this->packageManager = $packageManager;
        $this->eventBus = $eventBus;
        $this->fs = $fs;
        $this->container = $container;
    }
    
    /**
     * Returns the local storage filesystem instance
     *
     * @return \LaborDigital\T3ba\Core\VarFs\VarFs
     */
    public function getFs(): VarFs
    {
        return $this->fs;
    }
    
    /**
     * Returns the fs mount were ext config related data should be stored
     *
     * @return \LaborDigital\T3ba\Core\VarFs\Mount
     */
    public function getFsMount(): Mount
    {
        return $this->fs->getMount('ExtConfig');
    }
    
    /**
     * Returns the singleton of the ext config context object
     *
     * @return \LaborDigital\T3ba\ExtConfig\ExtConfigContext
     */
    public function getContext(): ExtConfigContext
    {
        if (isset($this->context)) {
            return $this->context;
        }
        
        $this->context = GeneralUtility::makeInstance(
            ExtConfigContext::class, $this
        );
        $this->context->initialize(new LoaderContext(), new ConfigState([]));
        
        return $this->context;
    }
    
    /**
     * Returns the loader instance that runs when the di container is build and/or instantiated
     *
     * @return \LaborDigital\T3ba\ExtConfig\Loader\DiLoader
     */
    public function getDiLoader(): DiLoader
    {
        return $this->loaders[static::DI_BUILD_LOADER_KEY] ??
               $this->loaders[static::DI_BUILD_LOADER_KEY]
                   = GeneralUtility::makeInstance(DiLoader::class, $this);
    }
    
    /**
     * Returns the loader instance that loads the main ext config files after ext_localconf.php has been loaded
     *
     * @return \LaborDigital\T3ba\ExtConfig\Loader\MainLoader
     */
    public function getMainLoader(): MainLoader
    {
        return $this->loaders[static::MAIN_LOADER_KEY] ??
               $this->loaders[static::MAIN_LOADER_KEY]
                   = GeneralUtility::makeInstance(MainLoader::class, $this, $this->eventBus);
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
        $loader = GeneralUtility::makeInstance(Loader::class, $type, (string)$appContext);
        $loader->setEventDispatcher($this->eventBus);
        $loader->setConfigContextClass(ExtConfigContext::class);
        $loader->setCache($this->fs->getCache());
        $loader->setContainer($this->container);
        
        if ($type === static::MAIN_LOADER_KEY) {
            $typoContext = $this->context->getTypoContext();
            $loader->setConfigFinder(
                $typoContext->di()->makeInstance(
                    ExtConfigConfigFinder::class,
                    [
                        $typoContext->site()->getAll(false),
                    ]
                )
            );
        }
        
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
     * Allows you to override a modified state object already stored in the cache.
     * BE CAREFUL WITH THIS! This is mostly an internal implementation detail to resolve
     * late bindings like in TCA definitions.
     *
     * In order to be persistable, the state MUST contain a cache key at the path defined in PERSISTABLE_STATE_PATH
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $state
     *
     * @throws \LaborDigital\T3ba\ExtConfig\UnpersistableCacheStateException
     */
    public function persistState(ConfigState $state): void
    {
        $cacheKey = $state->get(static::PERSISTABLE_STATE_PATH);
        if (! is_string($cacheKey)) {
            throw new UnpersistableCacheStateException(
                'The given cache state object could not be persisted, because there is no entry at path: "'
                . static::PERSISTABLE_STATE_PATH . '" that would contain the cache key');
        }
        
        $this->fs->getCache()->set($cacheKey, SerializerUtil::serializeJson($state->getAll()));
    }
    
    /**
     * Finds the list of all possible root locations and returns them
     * in form of an array, containing arrays with both the path and namespace generator
     *
     * @return array
     */
    public function getRootLocations(): array
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
     * Resolves the ext config namespace (containing the ext key and the vendor name) for a given class.
     * If Null is returned the class is not part of an extension we know of
     *
     * @param   string  $className
     *
     * @return string|null
     */
    public function resolveNamespaceForClass(string $className): ?string
    {
        if (isset($this->classNamespaceCache[$className])) {
            return $this->classNamespaceCache[$className];
        }
        
        $className = ltrim($className, '\\');
        foreach ($this->getExtKeyNamespaceMap() as $extKey => $namespaces) {
            foreach ($namespaces as $namespace => $_) {
                if (str_starts_with($className, $namespace)) {
                    $classParts = array_filter(explode('\\', $className));
                    if (count($classParts) === 1) {
                        return $this->classNamespaceCache[$className] = $extKey;
                    }
                    
                    return $this->classNamespaceCache[$className] = reset($classParts) . '.' . $extKey;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Resets all internal caches to an empty state
     *
     * @internal
     */
    public function reset(): void
    {
        $this->classNamespaceCache = [];
        $this->rootLocations = null;
        $this->loaders = [];
    }
    
    /**
     * Returns the namespace lists for the auto loader and the ext-key namespace map
     *
     * @return array[]
     */
    protected function getNamespaceMaps(): array
    {
        $cache = $this->fs->getCache();
        $cacheKey = 'namespaceMaps-' . $this->packageManager->getCacheIdentifier();
        
        if ($cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }
        
        $autoloadMap = [];
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
                    $potentialConfigDir = Path::join($package->getPackagePath(),
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
