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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);
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
 * Last modified: 2020.03.18 at 18:39
 */

namespace LaborDigital\T3ba\Core\CodeGeneration;

use Composer\Autoload\ClassLoader;
use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Core\VarFs\Mount;
use LaborDigital\T3ba\Event\ClassOverrideContentFilterEvent;
use LaborDigital\T3ba\Event\ClassOverrideStackFilterEvent;
use Neunerlei\FileSystem\Fs;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;
use TYPO3\ClassAliasLoader\ClassAliasMap;

class ClassOverrideGenerator
{
    
    /**
     * True if the init method ran at least once
     *
     * @var bool
     */
    protected static $initDone = false;
    
    /**
     * The temporary file system to store the class copies in
     *
     * @var Mount
     */
    protected static $fsMount;
    
    /**
     * The instance of the we use to resolve the class file names with
     *
     * @var ClassLoader
     */
    protected static $classLoader;
    
    /**
     * The list of class overrides that are registered
     *
     * @var array
     */
    protected static $overrideDefinitions = [];
    
    /**
     * Called once in the better api init boot phase to populate the required properties
     * and to register our event handler
     *
     * @param   \Composer\Autoload\ClassLoader       $composerClassLoader
     * @param   \LaborDigital\T3ba\Core\VarFs\Mount  $fsMount
     */
    public static function init(ClassLoader $composerClassLoader, Mount $fsMount): void
    {
        // Check if we already did the init process
        if (static::$initDone) {
            return;
        }
        static::$initDone = true;
        
        // Create local references
        static::$fsMount = $fsMount;
        static::$classLoader = $composerClassLoader;
        
        // Register autoload hook
        spl_autoload_register([static::class, 'loadClass'], false, true);
    }
    
    /**
     * Our own spl autoload function
     *
     * @param $class
     *
     * @return bool
     */
    public static function loadClass($class): bool
    {
        if (static::hasClassOverride($class)) {
            // Resolve the dependency list
            $classToOverrideWith = static::$overrideDefinitions[$class];
            $stack = [$class => $classToOverrideWith];
            for ($i = 0; $i < 100; $i++) {
                if (isset(static::$overrideDefinitions[$classToOverrideWith])) {
                    $tmp = $classToOverrideWith;
                    $classToOverrideWith = static::$overrideDefinitions[$classToOverrideWith];
                    $stack[$tmp] = $classToOverrideWith;
                } else {
                    break;
                }
            }
            
            // Resolve the stack
            $args['result'] = true;
            static::resolveOverrideStack($stack);
        }
        
        return false;
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
        if (class_exists($classToOverride, false)) {
            throw new ClassOverridesException(
                'The class: ' . $classToOverride . ' can not be overridden, because it is already defined!');
        }
        if (! $overrule && static::hasClassOverride($classToOverride)) {
            throw new ClassOverridesException(
                'The class: ' . $classToOverride . ' is already overridden with: '
                . static::$overrideDefinitions[$classToOverride] . ' and therefore can not be overwritten again!');
        }
        static::$overrideDefinitions[$classToOverride] = $classToOverrideWith;
    }
    
    /**
     * Returns true if the given class can be overwritten with something else
     *
     * @param   string  $classToOverride  The name of the class to check for
     * @param   bool    $withOverrule     Set this to true if you want allow overruling of the existing definition
     *
     * @return bool
     */
    public static function canOverrideClass(string $classToOverride, bool $withOverrule = false): bool
    {
        if (class_exists($classToOverride, false)) {
            return false;
        }
        if (! isset(static::$overrideDefinitions[$classToOverride])) {
            return true;
        }
        if ($withOverrule) {
            return true;
        }
        
        return false;
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
        return isset(static::$overrideDefinitions[$classToOverride]);
    }
    
    /**
     * Internal method which resolves the override stack compiles the required source code
     * and includes the generated files at runtime.
     *
     * @param   array  $stack  The list of steps that are required to resolve a class through
     *                         all it's overrides.
     *
     */
    protected static function resolveOverrideStack(array $stack): void
    {
        // Allow filtering
        TypoEventBus::getInstance()->dispatch(
            ($e = new ClassOverrideStackFilterEvent($stack))
        );
        $stack = $e->getStack();
        
        // Get the class names
        reset($stack);
        $initialClassName = key($stack);
        $finalClassName = end($stack);
        
        // Compile the copies
        $filesToInclude = [];
        foreach ($stack as $classToOverride => $classToOverrideWith) {
            $basename = Inflector::toFile($classToOverride);
            $cloneFilename = $basename . '-clone.php';
            $aliasFilename = $basename . '.php';
            $filesToInclude[] = $cloneFilename;
            $filesToInclude[] = $aliasFilename;
            
            // Check if we have to create the override
            if (! static::$fsMount->hasFile($aliasFilename) || ! static::$fsMount->hasFile($cloneFilename)) {
                // Make the class name
                $namespace = Path::classNamespace($classToOverride);
                $copyClassName = 'T3BaCopy' . Path::classBasename($classToOverride);
                $copyClassFullName = ltrim($namespace . '\\' . $copyClassName, '\\');
                
                // Create content
                $cloneContent = static::getClassCloneContentOf($classToOverride, $copyClassName);
                $aliasContent = static::getClassAliasContent($classToOverride, $classToOverrideWith, $finalClassName,
                    $copyClassFullName);
                
                // Allow filtering
                $e = new ClassOverrideContentFilterEvent(
                    $classToOverride,
                    $copyClassName,
                    $initialClassName,
                    $finalClassName,
                    $cloneContent,
                    $aliasContent
                );
                TypoEventBus::getInstance()->dispatch($e);
                $cloneContent = $e->getCloneContent();
                $aliasContent = $e->getAliasContent();
                
                // Dump the files
                static::$fsMount->setFileContent($cloneFilename, $cloneContent);
                static::$fsMount->setFileContent($aliasFilename, $aliasContent);
            }
        }
        
        // Include the files
        foreach ($filesToInclude as $aliasFilename) {
            static::$fsMount->includeFile($aliasFilename);
        }
        
        // Register alias map
        ClassAliasMap::addAliasMap([
            'aliasToClassNameMapping' => [
                $finalClassName => $initialClassName,
            ],
        ]);
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
     */
    protected static function getClassAliasContent(
        string $classToOverride,
        string $classToOverrideWith,
        string $finalClassName,
        string $copyClassFullName
    ): string
    {
        $namespace = Path::classNamespace($classToOverride);
        $baseName = Path::classBasename($classToOverride);
        
        return "<?php
declare(strict_types=1);
/**
 * CLASS OVERRIDE GENERATOR - GENERATED FILE
 * This file is generated dynamically! You should not edit it's contents,
 * because they will be lost as soon as composer autoload files are generated!
*/
/**
 * The original class can be found here:
 * @see \\$classToOverride
 *
 * The clone of the original class can be found here:
 * @see \\$copyClassFullName
 *
 * The class which is used as override can be found here:
 * @see \\$finalClassName
 */
Namespace $namespace;
if(!class_exists('\\$classToOverride', false)) {

    class $baseName
        extends \\$classToOverrideWith {}
}
";
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
     */
    protected static function getClassCloneContentOf(string $of, string $copyClassName): string
    {
        // Resolve the source file
        $overrideSourceFile = static::$classLoader->findFile($of);
        if ($overrideSourceFile === false) {
            throw new ClassOverridesException('Could not create a clone of class: ' . $of
                                              . ' because Composer could not resolve it\'s filename!');
        }
        
        // Load the content
        $source = Fs::readFileAsLines($overrideSourceFile);
        
        // Find matching class definition
        $className = Path::classBasename($of);
        $nameChanged = false;
        foreach ($source as $k => $line) {
            if (! preg_match('~(class\\s+)(.*?)(?:\\s*(?:;|$|{|\\n)|\\s+\\w)~si', ltrim($line), $m)) {
                continue;
            }
            if ($m[2] !== $className) {
                continue;
            }
            $nameChanged = true;
            $find = $m[1] . $m[2];
            $replaceWith = $m[1] . $copyClassName;
            $source[$k] = str_replace($find, $replaceWith, $line);
            break;
        }
        
        // Fail if we could not rewrite the class
        if (! $nameChanged) {
            throw new ClassOverridesException(
                'Failed to rewrite the name of class: ' . $className . ' to: ' .
                $copyClassName . ' when creating a copy of file: ' . $overrideSourceFile);
        }
        
        // Fix return types
        foreach ($source as $k => $line) {
            if (stripos($line, '@return') === false) {
                continue;
            }
            $pattern = '~(^\\s*\\*\\s*@return\\s+)' . preg_quote($className, '~') . '~si';
            $source[$k] = preg_replace($pattern, '$1' . $copyClassName, $line);
            $pattern = '~(^\\s*\\*\\s*@return\\s+)\\\\?' . preg_quote($of, '~') . '~si';
            $source[$k] = preg_replace($pattern, '$1' . $copyClassName, $source[$k]);
        }
        
        // Inject notice
        $noticeSet = false;
        foreach ($source as $k => $line) {
            // Fix __CLASS__ references
            $line = str_replace('__CLASS__', '\\' . rtrim($of, '\\') . '::class', $line);
            
            $sourceParsed[] = $line;
            if (! $noticeSet
                && ((stripos($line, '<?php') !== false) || (str_contains($line, '<?='))
                    || str_contains($line, '<?'))) {
                $sourceParsed[] = '/**' . PHP_EOL;
                $sourceParsed[] = ' * THIS FILE IS AUTOMATICALLY GENERATED!' . PHP_EOL;
                $sourceParsed[] = ' * ' . PHP_EOL;
                $sourceParsed[] = ' * This is a copy of the class: ' . $of . PHP_EOL;
                $sourceParsed[] = ' * Which normally resides in: ' . realpath($overrideSourceFile) . PHP_EOL;
                $sourceParsed[] = ' * ' . PHP_EOL;
                $sourceParsed[]
                    = ' * It was created by the T3BA extension in order to extend core functionality.'
                      . PHP_EOL;
                $sourceParsed[] = ' * NEVER, EVER EDIT THIS FILE MANUALLY - YOUR CHANGES WILL VANISH!' . PHP_EOL;
                $sourceParsed[] = ' * ' . PHP_EOL;
                $sourceParsed[] = " * @see \\$of" . PHP_EOL;
                $sourceParsed[] = ' */' . PHP_EOL . PHP_EOL;
                $noticeSet = true;
            }
            $source = $sourceParsed;
        }
        $source = implode($source);
        
        // Unlock all "private" methods to be "protected"...
        $source = preg_replace_callback('~(^|\\s|\\t)private(\\s(?:static\\s)?(?:\$|function))~i',
            static function ($m) {
                [, $before, $after] = $m;
                
                return $before . 'protected' . $after;
            }, $source);
        
        // Replace all "self::" references with "static::" to allow external overrides
        return preg_replace_callback('~(^|\\s|\\t)self::([$\w])~i',
            static function ($m) {
                [, $before, $after] = $m;
                
                return $before . 'static::' . $after;
            }, $source);
    }
}
