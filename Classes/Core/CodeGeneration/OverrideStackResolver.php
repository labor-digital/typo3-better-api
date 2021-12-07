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
 * Last modified: 2021.12.04 at 12:50
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Core\CodeGeneration;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Core\VarFs\Mount;
use LaborDigital\T3ba\Event\ClassOverrideContentFilterEvent;
use LaborDigital\T3ba\Event\ClassOverrideStackFilterEvent;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use Neunerlei\EventBus\EventBusInterface;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;

class OverrideStackResolver implements NoDiInterface
{
    
    /**
     * @var \Neunerlei\EventBus\EventBusInterface
     */
    protected $eventBus;
    
    /**
     * @var \LaborDigital\T3ba\Core\VarFs\Mount
     */
    protected $fsMount;
    
    /**
     * @var \LaborDigital\T3ba\Core\CodeGeneration\CodeGenerator
     */
    protected $codeGenerator;
    
    /**
     * @var \Closure
     */
    protected $codeGeneratorFactory;
    
    /**
     * Internal list to store the list of files to be included by the autoloader
     *
     * @var array
     */
    protected $includeList;
    
    /**
     * The list of already resolved aliases to avoid multiple executions
     *
     * @var array
     */
    protected $resolvedAliasMap = [];
    
    /**
     * True if the script is executed in phpunit
     *
     * @var bool
     */
    protected $isTestMode = false;
    
    public function __construct(EventBusInterface $eventBus, Mount $fsMount, \Closure $codeGeneratorFactory)
    {
        $this->eventBus = $eventBus;
        $this->fsMount = $fsMount;
        $this->codeGeneratorFactory = $codeGeneratorFactory;
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
     * Resolves the given stack of override dependencies, by creating the required override files
     * and automatically including them from their temporary sources.
     * The result is the definition for the TYPO3 ClassAliasMap
     *
     * @param   array  $stack
     *
     * @return array
     */
    public function resolve(array $stack): array
    {
        $this->eventBus->dispatch(
            ($e = new ClassOverrideStackFilterEvent($stack))
        );
        $stack = $e->getStack();
        
        $cacheKey = md5(SerializerUtil::serializeJson($stack) . '-' . $this->isTestMode);
        if (isset($this->resolvedAliasMap[$cacheKey])) {
            return $this->resolvedAliasMap[$cacheKey];
        }
        
        reset($stack);
        $initialClassName = key($stack);
        $finalClassName = end($stack);
        
        $this->includeList = [];
        foreach ($stack as $classToOverride => $classToOverrideWith) {
            $this->resolveStackEntry((string)$initialClassName, (string)$finalClassName, $classToOverride, $classToOverrideWith);
        }
        
        foreach ($this->includeList as $aliasFilename) {
            $this->fsMount->includeFile($aliasFilename);
        }
        $this->includeList = [];
        
        return $this->resolvedAliasMap[$cacheKey] = [$finalClassName => $initialClassName];
    }
    
    /**
     * Resolves a single stack entry by defining the required include files,
     * and creating a copy of the class if required
     *
     * @param   string  $initialClassName
     * @param   string  $finalClassName
     * @param   string  $classToOverride
     * @param   string  $classToOverrideWith
     */
    protected function resolveStackEntry(
        string $initialClassName,
        string $finalClassName,
        string $classToOverride,
        string $classToOverrideWith
    ): void
    {
        $basename = Inflector::toFile($classToOverride);
        $cloneFilename = $basename . '-clone.php';
        $aliasFilename = $basename . '.php';
        $this->includeList[] = $cloneFilename;
        $this->includeList[] = $aliasFilename;
        
        if (! $this->fsMount->hasFile($aliasFilename) || ! $this->fsMount->hasFile($cloneFilename)) {
            $namespace = Path::classNamespace($classToOverride);
            $copyClassName = 'T3BaCopy' . Path::classBasename($classToOverride);
            $copyClassFullName = ltrim($namespace . '\\' . $copyClassName, '\\');
            
            $codeGenerator = $this->getCodeGenerator();
            $cloneContent = $codeGenerator->getClassCloneContentOf(
                $classToOverride, $copyClassName);
            $aliasContent = $codeGenerator->getClassAliasContent(
                $classToOverride, $classToOverrideWith, $finalClassName, $copyClassFullName);
            
            $e = new ClassOverrideContentFilterEvent(
                $classToOverride,
                $copyClassName,
                $initialClassName,
                $finalClassName,
                $cloneContent,
                $aliasContent
            );
            $this->eventBus->dispatch($e);
            $cloneContent = $e->getCloneContent();
            $aliasContent = $e->getAliasContent();
            
            $this->fsMount->setFileContent($cloneFilename, $cloneContent);
            $this->fsMount->setFileContent($aliasFilename, $aliasContent);
        }
    }
    
    /**
     * Internal getter to resolve the code generator lazily
     *
     * @return \LaborDigital\T3ba\Core\CodeGeneration\CodeGenerator
     * @internal
     * @todo in v11 make this method protected
     */
    public function getCodeGenerator(): CodeGenerator
    {
        if (isset($this->codeGenerator)) {
            $c = $this->codeGenerator;
        } else {
            $c = $this->codeGenerator = ($this->codeGeneratorFactory)();
        }
        
        $c->setTestMode($this->isTestMode);
        
        return $c;
    }
}