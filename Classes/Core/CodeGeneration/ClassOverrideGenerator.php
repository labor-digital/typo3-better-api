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

namespace LaborDigital\T3ba\Core\CodeGeneration;

use Composer\Autoload\ClassLoader;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Core\VarFs\Mount;

class ClassOverrideGenerator implements NoDiInterface
{
    /**
     * @var \LaborDigital\T3ba\Core\CodeGeneration\AutoLoader
     */
    protected static $autoLoader;
    
    /**
     * True if the init method ran at least once
     *
     * @var bool
     * @deprecated will be removed in v11
     */
    protected static $initDone = false;
    
    /**
     * The temporary file system to store the class copies in
     *
     * @var Mount
     * @deprecated will be removed in v11
     */
    protected static $fsMount;
    
    /**
     * The instance of the T3 class loader we use, to resolve the class file names with
     *
     * @var ClassLoader
     * @deprecated will be removed in v11
     */
    protected static $classLoader;
    
    /**
     * The list of class overrides that are registered
     *
     * @var array
     * @deprecated will be removed in v11
     */
    protected static $overrideDefinitions = [];
    
    /**
     * True if the generator should handle "unit/functional test cases"
     *
     * @var bool
     * @deprecated will be removed in v11
     */
    protected static $isTestMode = false;
    
    /**
     * Called once in the better api init boot phase to populate the required properties
     * and to register our event handler
     *
     * @param   \LaborDigital\T3ba\Core\CodeGeneration\AutoLoader|mixed  $autoLoader
     * @param   bool|mixed                                               $testMode
     *
     * @todo the typeHints should be re-added in v11, they were removed for backward-compatability
     */
    public static function init($autoLoader, $testMode): void
    {
        if (! $autoLoader instanceof AutoLoader) {
            // @todo this part can be safely removed in v11
            if (static::$initDone) {
                return;
            }
            static::$initDone = true;
            
            $autoLoader = (function (ClassLoader $composerClassLoader, Mount $fsMount, ?bool $testMode = null) {
                static::$isTestMode = $testMode ?? str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'phpunit');
                
                return new AutoLoader(
                    new OverrideList(),
                    new OverrideStackResolver(
                        TypoEventBus::getInstance(),
                        $fsMount,
                        static function () use ($composerClassLoader) {
                            return new CodeGenerator($composerClassLoader);
                        }
                    ),
                    new LegacyContext(
                        $fsMount,
                        $composerClassLoader
                    )
                );
            })(...func_get_args());
            
            $autoLoader->setTestMode(static::$isTestMode);
        } else {
            $autoLoader->setTestMode((bool)$testMode);
            
            static::$isTestMode = (bool)$testMode;
        }
        
        if (isset(static::$autoLoader)) {
            static::$autoLoader->unregister();
        }
        
        static::$autoLoader = $autoLoader;
        $autoLoader->register();
        
        static::$fsMount = $autoLoader->legacyContext->fsMount;
        static::$classLoader = $autoLoader->legacyContext->classLoader;
    }
    
    /**
     * Returns the internal autoloader instance we use, to inject our clones
     *
     * @return \LaborDigital\T3ba\Core\CodeGeneration\AutoLoader
     */
    public static function getAutoLoader(): AutoLoader
    {
        return static::$autoLoader;
    }
    
    /**
     * Our own spl autoload function
     *
     * @param $class
     *
     * @return bool
     * @deprecated will be removed in v11
     */
    public static function loadClass($class): bool
    {
        return static::$autoLoader->loadClass($class);
    }
    
    /**
     * Registers a new class override. The override will completely replace the original source class.
     * The overwritten class will be copied and is available in the same namespace but with the
     * "T3baCopy" prefix in front of it's class name. The overwritten class has all it's private
     * properties and function changed to protected for easier overrides.
     *
     * This method throws an exception if the class is already overwritten by another class
     *
     * @param   string  $classToOverride      The name of the class to overwrite with the class given in
     *                                        $classToOverrideWith
     * @param   string  $classToOverrideWith  The name of the class that should be used instead of the class defined as
     *                                        $classToOverride
     * @param   bool    $overrule             If this is set to true already registered overrides can be changed to a
     *                                        different definition
     *
     * @throws \LaborDigital\T3ba\Core\CodeGeneration\ClassOverridesException
     */
    public static function registerOverride(
        string $classToOverride,
        string $classToOverrideWith,
        bool $overrule = false
    ): void
    {
        static::$autoLoader->getOverrideList()->registerOverride(...func_get_args());
    }
    
    /**
     * Returns true if the given class can be overwritten with something else
     *
     * @param   string  $classToOverride  The name of the class to check for
     * @param   bool    $withOverrule     Set this to true if you want to allow overruling of the existing definition
     *
     * @return bool
     */
    public static function canOverrideClass(string $classToOverride, bool $withOverrule = false): bool
    {
        return static::$autoLoader->getOverrideList()->canOverrideClass(...func_get_args());
    }
    
    /**
     * Returns true if the class with the given name is registered as override
     *
     * @param   string  $classToOverride  The name of the class to check for
     *
     * @return bool
     */
    public static function hasClassOverride(string $classToOverride): bool
    {
        return static::$autoLoader->getOverrideList()->hasClassOverride(...func_get_args());
    }
    
    /**
     * Internal method which resolves the override stack compiles the required source code
     * and includes the generated files at runtime.
     *
     * @param   array  $stack  The list of steps that are required to resolve a class through
     *                         all it's overrides.
     *
     * @deprecated will be removed in v11
     */
    protected static function resolveOverrideStack(array $stack): void
    {
        static::$autoLoader->legacyHandler(__FUNCTION__, func_get_args());
    }
    
    /**
     * Generates the class alias file content and returns it
     *
     * @param   string  $classToOverride
     * @param   string  $classToOverrideWith
     * @param   string  $finalClassName
     * @param   string  $copyClassFullName
     *
     * @return string
     * @deprecated will be removed in v11
     */
    protected static function getClassAliasContent(
        string $classToOverride,
        string $classToOverrideWith,
        string $finalClassName,
        string $copyClassFullName
    ): string
    {
        return static::$autoLoader->legacyHandler(__FUNCTION__, func_get_args());
    }
    
    /**
     * This internal helper is used to read the source code of a given class, and create a copy out of it.
     * The copy has a unique name and all references, like return types and type hints will be replaced by said, new
     * name.
     *
     * @param   string  $of             The real name of the class to create a copy of
     * @param   string  $copyClassName  The new name of the class after the copy took place
     *
     * @return string
     * @throws \LaborDigital\T3ba\Core\CodeGeneration\ClassOverridesException
     * @deprecated will be removed in v11
     */
    protected static function getClassCloneContentOf(string $of, string $copyClassName): string
    {
        return static::$autoLoader->legacyHandler(__FUNCTION__, func_get_args());
    }
}
