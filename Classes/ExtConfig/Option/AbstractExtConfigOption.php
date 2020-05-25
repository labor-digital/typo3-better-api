<?php
/**
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
 * Last modified: 2020.03.20 at 12:16
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option;

use Composer\Autoload\ClassMapGenerator;
use LaborDigital\Typo3BetterApi\Event\Events\ExtConfigCachedValueFilterEvent;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\FileSystem\Fs;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;

abstract class AbstractExtConfigOption implements ExtConfigOptionInterface
{
    
    /**
     * @var \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
     */
    protected $context;
    
    /**
     * Stores the configuration for the cached value generators
     * @var array
     */
    protected $__cachedValueConfig = [];
    
    /**
     * A list of child options that were created by this option
     * @var \LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractChildExtConfigOption[]
     */
    protected $__children = [];
    
    /**
     * @inheritDoc
     */
    public function setContext(ExtConfigContext $context)
    {
        $this->context = $context;
    }
    
    /**
     * @inheritDoc
     */
    public function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        // Silence by default
    }
    
    /**
     * Returns the current list of elements that are registered for the cached value generator of a certain $key
     *
     * @param string $key The unique key of the cached value to retrieve
     *
     * @return array
     * @see getCachedValueOrRun
     */
    protected function getCachedValueConfig(string $key): array
    {
        return Arrays::getPath($this->__cachedValueConfig, [$key], []);
    }
    
    /**
     * This method is used in combination with getCachedValueOrRun.
     * It collects data (what kind is up to you) for a certain cache key. If said cache key is required by
     * getCachedValueOrRun and no cache entry exists for the key, the generator will receive the collected data as base
     * for the value generation.
     *
     * @param string      $key       The key of the cached configuration to set this value for
     * @param mixed       $value     Any kind of value
     * @param string|null $uniqueKey Can be used if you want to add "unique" values. Unique values will be overwritten
     *                               if they already exist in the list of values, non unique values will simply be
     *                               added to the list
     *
     * @return $this
     * @see getCachedValueOrRun
     */
    protected function addToCachedValueConfig(string $key, $value, ?string $uniqueKey = null)
    {
        $data = [
            'value'  => $this->replaceMarkers($value),
            'extKey' => $this->context->getExtKey(),
            'vendor' => $this->context->getVendor(),
        ];
        if (!empty($uniqueKey)) {
            $this->__cachedValueConfig[$key][$uniqueKey] = $data;
        } else {
            $this->__cachedValueConfig[$key][] = $data;
        }
        return $this;
    }
    
    /**
     * Can be used to remove a previously defined configuration set by addToCachedValueConfig().
     * This will only work if you supplied addToCachedValueConfig() with a $uniqueKey.
     *
     * @param string $key       The key of the cached configuration to remove the value from
     * @param string $uniqueKey The unique key to remove from the given configuration list
     *
     * @return $this
     */
    protected function removeFromCachedValueConfig(string $key, string $uniqueKey)
    {
        if (!is_array($this->__cachedValueConfig[$key])) {
            return $this;
        }
        Arrays::removePath($this->__cachedValueConfig[$key], [$uniqueKey]);
        return $this;
    }
    
    /**
     * To avoid as much overhead as possible during runtime we try to cache as much of the configuration
     * and it's repetitive tasks as we can. The getCachedValueOrRun method in combination with addToCachedValueConfig
     * comes to the rescue in that regard.
     *
     * It will check if there is a cached value (presumably the result of a load heavy conversion) and if it exists, it
     * will return it. However, if it does not exist this method will execute the given generator
     * function/method/closure that contains all the logic to generate said cached value.
     *
     * To collect the values that are probably required by the generator you can use the addToCachedValueConfig()
     * method. It will accept any kind of value and store it in an array of data for a certain $key (the same as you
     * passed to this method). When a generator runs it will receive this list of data and can use it to perform
     * its (load heavy) transformation. The result of the generator is cached and returned. So you can always be sure
     * that the value you require exists.
     *
     * Note: If you pass a generator like: [$CLASSNAME, "method"] it is NOT handled using a static call (the default in
     * PHP) but we use the typo3 container to create an instance of $CLASSNAME and call the method on it. So all the
     * fancy extbase dependency injections works in generators as well.
     *
     * Note2: Make sure that the result of $generator can be serialized! Otherwise this will fail!
     *
     * @param string          $key            The unique key of the cached value
     * @param callable|string $generator      Either a php callable or the class name of a class that implements
     *                                        ExtConfigCachedValueGeneratorInterface
     * @param array           $additionalData Additional data to be passed along to the generator
     *
     * @return mixed
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     * @see \LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption::runCachedValueGenerator()
     */
    protected function getCachedValueOrRun(string $key, $generator, array $additionalData = [])
    {
        // Inject the cache file name in the additional data, which tells runCachedValueGenerator to enable caching
        $additionalData['@cacheFileName'] = $this->makeCacheFileName($key);
        return $this->runCachedValueGenerator($key, $generator, $additionalData);
    }
    
    /**
     * Behaves exactly the same way as getCachedValueOrRun() but will not use the caching system in any way.
     * It will run the generator every time you invoke this method.
     *
     * @param string          $key            The unique key of the cached value
     * @param callable|string $generator      Either a php callable or the class name of a class that implements
     *                                        ExtConfigCachedValueGeneratorInterface
     * @param array           $additionalData Additional data to be passed along to the generator
     *
     * @return mixed
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     * @see \LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption::getCachedValueOrRun()
     */
    protected function runCachedValueGenerator(string $key, $generator, array $additionalData = [])
    {
        // Check if we have generators for this value
        $cacheFileName = is_string($additionalData['@cacheFileName']) ? $additionalData['@cacheFileName'] : null;
        unset($additionalData['@cacheFileName']);
        $useCache = !is_null($cacheFileName);
        $config = $this->getCachedValueConfig($key);
        
        // Check if we have a cached value for this key
        if ($useCache && $this->context->Fs->hasFile($cacheFileName)) {
            $result = $this->context->Fs->getFileContent($cacheFileName);
            unset($this->__cachedValueConfig[$key]);
            return $result;
        }
        
        // Check if we got a generator class
        if (is_string($generator) && class_exists($generator) && in_array(CachedValueGeneratorInterface::class, class_implements($generator))) {
            $generator = [$generator, 'generate'];
        }
        
        // Check if the generator is a class reference we have to create
        if (is_array($generator) && count($generator) === 2 && is_string($generator[0]) && class_exists($generator[0])) {
            $generator[0] = $this->context->getInstanceOf($generator[0]);
        }
        
        // Check if the generator is callable
        if (!is_callable($generator)) {
            throw new ExtConfigException('The given generator is not callable!');
        }
        
        // Call the generator
        $result = call_user_func($generator, $config, $this->context, $additionalData, $this);
        
        // Allow filtering
        $this->context->EventBus->dispatch(($e = new ExtConfigCachedValueFilterEvent(
            $result,
            $cacheFileName,
            $key,
            $generator,
            $additionalData,
            $this->context,
            $this,
            $config,
            $useCache
        )));
        $result = $e->getResult();
        $useCache = $e->isUseCache();
        
        // Put it into storage
        if ($useCache) {
            $this->context->Fs->setFileContent($cacheFileName, $result);
            unset($this->__cachedValueConfig[$key]);
        }
        
        // Done
        return $result;
    }
    
    /**
     * Adds a "registration" step to the stacked generation process with the given $stackKey.
     * Registrations are always called before overrides. Both are executed as first call first serve.
     *
     * As a rule of thumb: Use registrations if you are sure you are creating a certain element,
     * use override if you modify an existing element (which was previously registered).
     *
     * @param string $stackKey           The stack id which holds the element list
     * @param string $elementKey         A unique id for the element you want to configure with $configurationClass
     * @param string $configurationClass A configuration class that is passed to the configuration generator and
     *
     * @return $this
     */
    protected function addRegistrationToCachedStack(string $stackKey, string $elementKey, string $configurationClass)
    {
        return $this->addToCachedValueConfig('element' . $stackKey, [
            'key'   => $elementKey,
            'class' => $configurationClass,
        ], $elementKey . $configurationClass);
    }
    
    /**
     * This helper can be used to add a whole directory of registrations / overrides to a given stack.
     * The script will search all classes in the given directory (not recursive!) and will try to find configuration
     * classes among them.
     *
     * As this is a highly automated progress you have to define some rules on what to include and how the found class
     * translates to an element key.
     *
     * For that you will have to provide two callbacks. The first the $elementFilter is called once for every class we
     * find in the given directory. It should decide if the class is viable to be added to the stack or not. If the
     * function returns true it is added, if the function returns false it is not. The second callback
     * $elementKeyProvider, also is called once for every class. It should be able to convert the given class to an
     * element name (e.g. a class to a plugin name).
     *
     * @param string   $stackKey           The stack id which holds the element list
     * @param string   $directoryPath      The path to the directory to add either a full path or an EXT:... path
     * @param callable $elementFilter      The filter callback to check if a class can be added to the stack or not
     * @param callable $elementKeyProvider The key provider to convert the class name into an element name
     * @param bool     $asOverrides        True if the given directory should be loaded as overrides
     *
     * @return $this
     */
    protected function addDirectoryToCachedStack(string $stackKey, string $directoryPath, callable $elementFilter, callable $elementKeyProvider, bool $asOverrides = false)
    {
        // Get list of classes in the directory
        $directoryPath = $this->replaceMarkers($directoryPath);
        $elementClassList = $this->getCachedValueOrRun(
            'directory' . $stackKey . $directoryPath . ($asOverrides ? '.override' : ''),
            function () use ($directoryPath, $elementFilter, $elementKeyProvider) {
                $directoryPath = $this->context->TypoContext->getPathAspect()->typoPathToRealPath($directoryPath);
                $classMap = ClassMapGenerator::createMap(Fs::getDirectoryIterator($directoryPath));
                
                // Loop through the class map
                $elementClassList = [];
                foreach ($classMap as $class => $filename) {
                    if (empty(call_user_func($elementFilter, $class, $filename, $directoryPath))) {
                        continue;
                    }
                    $elementKey = call_user_func($elementKeyProvider, $class, $filename, $directoryPath);
                    if (empty($elementKey)) {
                        throw new ExtConfigException("Failed to generate an element key for class: $class");
                    }
                    $elementClassList[] = [$elementKey, $class];
                }
                return $elementClassList;
            }
        );
        foreach ($elementClassList as $row) {
            if ($asOverrides) {
                $this->addOverrideToCachedStack($stackKey, ...$row);
            } else {
                $this->addRegistrationToCachedStack($stackKey, ...$row);
            }
        }
        
        return $this;
    }
    
    /**
     * Adds a "override" step to the stacked generation process with the given $stackKey.
     * overrides are always called after registrations. Both are executed as first call first serve.
     *
     * As a rule of thumb: Use registrations if you are sure you are creating a certain element,
     * use override if you modify an existing element (which was previously registered).
     *
     * @param string $stackKey           The stack id which holds the element list
     * @param string $elementKey         A unique id for the element you want to configure with $configurationClass
     * @param string $configurationClass A configuration class that is passed to the configuration generator and
     *
     * @return $this
     */
    protected function addOverrideToCachedStack(string $stackKey, string $elementKey, string $configurationClass)
    {
        return $this->addToCachedValueConfig('element' . $stackKey . '.override', [
            'key'   => $elementKey,
            'class' => $configurationClass,
        ], $elementKey . $configurationClass);
    }
    
    /**
     * Removes a registered step either from the registration or from the override stack.
     *
     * @param string $stackKey           The stack id which holds the element list
     * @param string $elementKey         A unique id for the element you want to remove the $configurationClass from
     * @param string $configurationClass A configuration class that should be removed from the stack
     * @param bool   $overrides          If this is true the step will be removed from the overrides instead of the
     *                                   registrations
     *
     * @return $this
     */
    protected function removeFromCachedStack(string $stackKey, string $elementKey, string $configurationClass, bool $overrides = false)
    {
        return $this->removeFromCachedValueConfig('element' . $stackKey . ($overrides ? '.override' : ''), $elementKey . $configurationClass);
    }
    
    /**
     * Mostly internal but you may use it to get the call stack either for the registrations or the overrides of
     * the elements registered with the given $stackKey
     *
     * @param string $stackKey      The stack id you want to retrieve the step definitions for
     * @param bool   $overrides     Ths method returns the registrations by default, if you want the overrides
     *                              instead, set this to true
     *
     * @return array
     */
    protected function getCachedStackDefinitions(string $stackKey, bool $overrides = false): array
    {
        $definitions = $this->getCachedValueConfig('element' . $stackKey . ($overrides ? '.override' : ''));
        $definitionsSorted = [];
        foreach ($definitions as $def) {
            $definitionsSorted[$def['value']['key']][] = [
                'value'  => $def['value']['class'],
                'extKey' => $def['extKey'],
                'vendor' => $def['vendor'],
            ];
        }
        return $definitionsSorted;
    }
    
    /**
     * Similar to getCachedStackDefinitions() but returns the whole stack (registrations and overrides)
     * combined as a result.
     *
     * @param string $stackKey The stack id you want to retrieve the step definitions for
     *
     * @return array
     */
    protected function getCachedStack(string $stackKey): array
    {
        // Get the lists
        $regs = $this->getCachedStackDefinitions($stackKey);
        $mods = $this->getCachedStackDefinitions($stackKey, true);
        
        // Build the element stack
        $stack = [];
        foreach ($regs as $elementKey => $localStack) {
            // Add overrides to local stack
            if (!empty($mods[$elementKey])) {
                $localStack = Arrays::attach($localStack, $mods[$elementKey]);
            }
            
            // Add to main stack
            $stack[$elementKey] = $localStack;
        }
        
        // Done
        return $stack;
    }
    
    /**
     * Similar to (and in fact build on top of) getCachedValueOrRun() but it has a slightly different mechanic.
     *
     * While getCachedValueOrRun() is mend to be used when your config option retrieves some kind of arguments directly
     * (at run time) You may use the "stack" value generation if you instead want to collect the configuration using
     * classes instead.
     *
     * The main idea is that you have a "registration" and a "override" phase. Both run after each other,
     * while the registration comes always first the override will come always as second.
     *
     * As a rule of thumb: Use registrations if you are sure you are creating a certain element,
     * use override if you modify an existing element (which was previously registered).
     *
     * Stacked values are designed for complex or bulky configurations that are better put into their own config files.
     *
     * @param string $listKey        The stack id you want to retrieve the value for
     * @param string $cachedStackGeneratorClass
     * @param array  $additionalData Additional data that is transferred to the generator class
     *
     * @return mixed
     */
    protected function getCachedStackValueOrRun(string $listKey, string $cachedStackGeneratorClass, array $additionalData = [])
    {
        return $this->getCachedValueOrRun('element' . $listKey, function () use ($listKey, $cachedStackGeneratorClass, $additionalData) {
            return $this->runCachedStackGenerator($listKey, $cachedStackGeneratorClass, $additionalData);
        });
    }
    
    /**
     * The counterpart to getCachedStackValueOrRun() that will call the stack generator every time it is executed.
     * The resulting value will be returned but not cached.
     *
     * @param string $listKey        The stack id you want to retrieve the value for
     * @param string $cachedStackGeneratorClass
     * @param array  $additionalData Additional data that is transferred to the generator class
     *
     * @return mixed
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     */
    protected function runCachedStackGenerator(string $listKey, string $cachedStackGeneratorClass, array $additionalData = [])
    {
        // Make and validate the generator instance
        $generator = $this->context->getInstanceOf($cachedStackGeneratorClass);
        if (!$generator instanceof CachedStackGeneratorInterface) {
            throw new ExtConfigException("Invalid generator class $cachedStackGeneratorClass given! It does not implement the required interface: " . CachedStackGeneratorInterface::class . '!');
        }
        
        // Run the generator for the build stack
        return $generator->generate($this->getCachedStack($listKey), $this->context, $additionalData, $this);
    }
    
    /**
     * Shortcut to ExtConfigContext::replaceMarkers()
     *
     * @param array|mixed $raw The value which should be traversed for markers
     *
     * @return array|mixed
     * @see ExtConfigContext::replaceMarkers()
     */
    protected function replaceMarkers($raw)
    {
        return $this->context->replaceMarkers($raw);
    }
    
    /**
     * This generic helper can be used to create a child option instance for this config option.
     * This can be used to split up your configuration object into smaller classes that are easier to handle.
     * All children will automatically be handled as "Singleton" inside this option object, as long as you
     * are using this method to look up the instances
     *
     * @param string $childOptionClass The class name of the class you want to instantiate.
     *                                 The class has to extend the AbstractChildExtConfigOption class
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractChildExtConfigOption|mixed
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     */
    protected function getChildOptionInstance(string $childOptionClass): AbstractChildExtConfigOption
    {
        if (isset($this->__children[$childOptionClass])) {
            return $this->__children[$childOptionClass];
        }
        if (!in_array(AbstractChildExtConfigOption::class, class_parents($childOptionClass))) {
            throw new ExtConfigException('Could not create a child for the option: ' . get_called_class() . ' of type: ' .
                $childOptionClass . ' because it does not extend the ' . AbstractChildExtConfigOption::class . ' class');
        }
        return $this->__children[$childOptionClass] = call_user_func([$childOptionClass, 'makeInstance'], $this);
    }
    
    /**
     * Internal helper to create a unique cache file name for this object
     *
     * @param string $key
     *
     * @return string
     */
    protected function makeCacheFileName(string $key): string
    {
        return Inflector::toFile(Path::classBasename(get_called_class()) . '_' . md5(get_called_class())) .
            DIRECTORY_SEPARATOR . Inflector::toFile($key) . '.txt';
    }
}
