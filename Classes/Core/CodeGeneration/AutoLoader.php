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
 * Last modified: 2021.12.06 at 09:47
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Core\CodeGeneration;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use TYPO3\ClassAliasLoader\ClassAliasMap;

class AutoLoader implements NoDiInterface
{
    /**
     * @var \LaborDigital\T3ba\Core\CodeGeneration\LegacyContext
     * @deprecated temporary implementation detail, which will be removed in v11
     */
    public $legacyContext;
    
    /**
     * @var \LaborDigital\T3ba\Core\CodeGeneration\OverrideList
     */
    protected $overrideList;
    
    /**
     * @var \LaborDigital\T3ba\Core\CodeGeneration\OverrideStackResolver
     */
    protected $stackResolver;
    
    /**
     * True if the autoloader was registered, false if not
     *
     * @var bool
     */
    protected $isRegistered = false;
    
    public function __construct(
        OverrideList $overrideList,
        OverrideStackResolver $stackResolver,
        LegacyContext $legacyContext
    )
    {
        $this->overrideList = $overrideList;
        $this->stackResolver = $stackResolver;
        $this->legacyContext = $legacyContext;
        
        $overrideList->setAutoLoader($this);
    }
    
    /**
     * Registers the autoloader in the system
     */
    public function register(): void
    {
        if ($this->isRegistered) {
            return;
        }
        
        spl_autoload_register([$this, 'loadClass'], false, true);
        $this->isRegistered = true;
    }
    
    /**
     * Removes the autoloader from the system
     */
    public function unregister(): void
    {
        if (! $this->isRegistered) {
            return;
        }
        
        spl_autoload_unregister([$this, 'loadClass']);
        $this->isRegistered = false;
    }
    
    /**
     * Returns the instance of the override list which is used to resolve our overrides
     *
     * @return \LaborDigital\T3ba\Core\CodeGeneration\OverrideList
     */
    public function getOverrideList(): OverrideList
    {
        return $this->overrideList;
    }
    
    /**
     * Used to toggle the internal test mode flag
     *
     * @param   bool  $isTestMode
     */
    public function setTestMode(bool $isTestMode): void
    {
        $this->overrideList->setTestMode($isTestMode);
        $this->stackResolver->setTestMode($isTestMode);
    }
    
    /**
     * Our own spl autoload function
     *
     * @param $class
     *
     * @return bool
     */
    public function loadClass($class): bool
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return false;
        }
        
        $stack = $this->overrideList->getClassStack($class);
        if (is_array($stack)) {
            $aliasMap = $this->stackResolver->resolve($stack);
            ClassAliasMap::addAliasMap(['aliasToClassNameMapping' => $aliasMap]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Gives access to internal processes to the outside world to allow legacy methods
     * to continue to work as expected
     *
     * @param   string  $action
     * @param   array   $args
     *
     * @return string|null
     * @deprecated Temporary implementation detail, will be removed in v11
     */
    public function legacyHandler(string $action, array $args)
    {
        switch ($action) {
            case 'resolveOverrideStack':
                [$stack] = $args;
                if (is_array($stack)) {
                    $aliasMap = $this->stackResolver->resolve($stack);
                    ClassAliasMap::addAliasMap(['aliasToClassNameMapping' => $aliasMap]);
                }
                break;
            case 'getClassAliasContent':
                return $this->stackResolver->getCodeGenerator()->getClassAliasContent(...$args);
            case 'getClassCloneContentOf':
                return $this->stackResolver->getCodeGenerator()->getClassCloneContentOf(...$args);
        }
        
        return null;
    }
}