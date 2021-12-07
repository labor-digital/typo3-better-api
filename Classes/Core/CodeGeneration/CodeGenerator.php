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
 * Last modified: 2021.12.04 at 12:52
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Core\CodeGeneration;


use Composer\Autoload\ClassLoader;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use Neunerlei\FileSystem\Fs;
use Neunerlei\PathUtil\Path;

class CodeGenerator implements NoDiInterface
{
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    protected $composerClassLoader;
    
    /**
     * True if the script is executed in phpunit
     *
     * @var bool
     */
    protected $isTestMode = false;
    
    public function __construct(ClassLoader $composerClassLoader)
    {
        $this->composerClassLoader = $composerClassLoader;
    }
    
    /**
     * Used to toggle the internal test mode flag
     *
     * @param   bool  $isTestMode
     */
    public function setTestMode(bool $isTestMode): void
    {
        $this->isTestMode = $isTestMode;
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
    public function getClassAliasContent(
        string $classToOverride,
        string $classToOverrideWith,
        string $finalClassName,
        string $copyClassFullName
    ): string
    {
        $namespace = Path::classNamespace($classToOverride);
        $baseName = Path::classBasename($classToOverride);
        
        return <<<PHP
<?php
declare(strict_types=1);
/**
 * CLASS OVERRIDE GENERATOR - GENERATED FILE
 * This file is generated dynamically! You should not edit its contents,
 * because they will be lost as soon as the TYPO3 cache is cleared
 *
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
PHP;
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
    public function getClassCloneContentOf(string $of, string $copyClassName): string
    {
        // Resolve the source file
        $overrideSourceFile = $this->composerClassLoader->findFile($of);
        if (($overrideSourceFile === false) && ! $this->isTestMode) {
            throw new ClassOverridesException('Could not create a clone of class: ' . $of
                                              . ' because Composer could not resolve it\'s filename!');
        }
        
        $source = $this->readSource((string)$overrideSourceFile, $of);
        $source = $this->fixRenameClass($source, $of, $copyClassName);
        $source = $this->fixReturnTypes($source, $of, $copyClassName);
        $source = $this->fixInjectNotice($source, $of);
        
        // Now, some good old string manipulation
        $source = implode($source);
        $source = $this->fixSelfReferences($source, $of);
        $source = $this->fixUnlockPrivateChildren($source);
        $source = $this->fixRemoveFinalModifier($source);
        
        return $source;
    }
    
    /**
     * Reads the source of a class as an array of lines
     *
     * @param   string  $overrideSourceFile  The file which contains the class
     * @param   string  $of                  The name of the class to clone
     *
     * @return string[]
     */
    protected function readSource(string $overrideSourceFile, string $of): array
    {
        if ($this->isTestMode && empty($overrideSourceFile)) {
            // While it looks odd, this is the only way to ensure that the line-endings match between the test files
            $nl = '
';
            
            return [
                '<?php' . $nl,
                'namespace ' . Path::classNamespace($of) . ';' . $nl,
                'class ' . Path::classBasename($of) . '{}' . $nl,
            ];
        }
        
        return Fs::readFileAsLines($overrideSourceFile);
    }
    
    /**
     * Renames the required class to our given $copyClassName
     *
     * @param   array   $source
     * @param   string  $of
     * @param   string  $copyClassName
     *
     * @return array
     * @throws \LaborDigital\T3ba\Core\CodeGeneration\ClassOverridesException
     */
    protected function fixRenameClass(array $source, string $of, string $copyClassName): array
    {
        $className = Path::classBasename($of);
        $nameChanged = false;
        foreach ($source as $k => $line) {
            if (! preg_match('~(class\\s+)(.*?)(?:\\s*(?:;|$|{|\\n)|\\s+\\w|\\s+})~si', ltrim($line), $m)) {
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
                $copyClassName . ' when creating a copy of class: ' . $of);
        }
        
        return $source;
    }
    
    /**
     * Rewrites the (at)return types for the methods of the class
     *
     * @param   array   $source
     * @param   string  $of
     * @param   string  $copyClassName
     *
     * @return array
     */
    protected function fixReturnTypes(array $source, string $of, string $copyClassName): array
    {
        $className = Path::classBasename($of);
        foreach ($source as $k => $line) {
            if (stripos($line, '@return') === false) {
                continue;
            }
            $pattern = '~(^\\s*\\*\\s*@return\\s+)' . preg_quote($className, '~') . '~si';
            $source[$k] = preg_replace($pattern, '$1' . $copyClassName, $line);
            $pattern = '~(^\\s*\\*\\s*@return\\s+)\\\\?' . preg_quote($of, '~') . '~si';
            $source[$k] = preg_replace($pattern, '$1' . $copyClassName, $source[$k]);
        }
        
        return $source;
    }
    
    /**
     * Injects the copy notice after the first opening PHP tag
     *
     * @param   array   $source
     * @param   string  $of
     *
     * @return array
     */
    protected function fixInjectNotice(array $source, string $of): array
    {
        $linesBefore = [];
        foreach ($source as $k => $line) {
            if (str_contains($line, '<?php') || str_contains($line, '<?=') || str_contains($line, '<?')) {
                $notice = <<<PHP
/**
 * CLASS OVERRIDE GENERATOR - GENERATED FILE
 * This file is generated dynamically! You should not edit its contents,
 * because they will be lost as soon as the TYPO3 cache is cleared
 *
 * THIS FILE IS AUTOMATICALLY GENERATED!
 *
 * This is a copy of the class: $of
 *
 * It was created by the T3BA extension in order to extend core functionality.
 *
 * @see $of
 */

PHP;
                
                return array_merge(
                    $linesBefore,
                    [$line],
                    [$notice],
                    array_slice($source, $k + 1)
                );
            }
            
            // Theoretically this should never happen...
            // @codeCoverageIgnoreStart
            $linesBefore[] = $line;
            // @codeCoverageIgnoreEnd
        }
        
        // Neither should this, but I keep it as a backup if there is somehow HTML before the PHP code
        // @codeCoverageIgnoreStart
        return $source;
        // @codeCoverageIgnoreEnd
    }
    
    /**
     * Unlocks all "private" methods and properties to be "protected"
     *
     * @param   string  $source
     *
     * @return string
     */
    protected function fixUnlockPrivateChildren(string $source): string
    {
        return preg_replace_callback('~(^|\\s|\\t)private(\\s+(?:static\\s|final\\s)?(?:\$|function|const))~i',
            static function ($m) {
                [, $before, $after] = $m;
                
                return $before . 'protected' . $after;
            }, $source);
    }
    
    /**
     * Resolves all self references of the class to the new copy
     *
     * @param   string  $source
     * @param   string  $of
     *
     * @return string
     */
    protected function fixSelfReferences(string $source, string $of): string
    {
        $source = str_replace('__CLASS__', '\\' . rtrim($of, '\\') . '::class', $source);
        
        // Replace all "self::" references with "static::" to allow external overrides
        return preg_replace_callback('~(^|\\s|\\t)self::([$\w])~i',
            static function ($m) {
                [, $before, $after] = $m;
                
                return $before . 'static::' . $after;
            }, $source);
    }
    
    /**
     * Removes all "final" modifiers from the class declaration and methods
     *
     * @param   string  $source
     *
     * @return string
     */
    protected function fixRemoveFinalModifier(string $source): string
    {
        return preg_replace(
            '~(^|\\s|\\t)final\\s+((?:protected\\s|private\\s|public\\s|static\\s|abstract\\s)*(?:function|class))~',
            '$1$2',
            $source
        );
    }
}