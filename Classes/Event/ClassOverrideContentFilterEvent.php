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

namespace LaborDigital\T3BA\Event;

/**
 * Class ClassOverrideContentFilterEvent
 *
 * Can be used to modify the content of both the class copy and the class alias before the files are dumped
 *
 * @package LaborDigital\T3BA\Core\Event
 */
class ClassOverrideContentFilterEvent
{
    
    /**
     * The current class in the stack that should be overwritten
     *
     * @var string
     */
    protected $classNameToOverride;
    
    /**
     * The name of the generated copy of the class
     *
     * @var string
     */
    protected $copyClassName;
    
    /**
     * The first name in the stack that is overwritten
     *
     * @var string
     */
    protected $initialClassName;
    
    /**
     * The last name in the stack that is overwritten
     *
     * @var string
     */
    protected $finalClassName;
    
    /**
     * The content of the current copy that is created
     *
     * @var string
     */
    protected $cloneContent;
    
    /**
     * The content of the class alias that is created to link the new class with the actual class name
     *
     * @var string
     */
    protected $aliasContent;
    
    /**
     * ClassOverrideContentFilterEvent constructor.
     *
     * @param   string  $classNameToOverride
     * @param   string  $copyClassName
     * @param   string  $initialClassName
     * @param   string  $finalClassName
     * @param   string  $cloneContent
     * @param   string  $aliasContent
     */
    public function __construct(
        string $classNameToOverride,
        string $copyClassName,
        string $initialClassName,
        string $finalClassName,
        string $cloneContent,
        string $aliasContent
    )
    {
        $this->classNameToOverride = $classNameToOverride;
        $this->copyClassName = $copyClassName;
        $this->initialClassName = $initialClassName;
        $this->finalClassName = $finalClassName;
        $this->cloneContent = $cloneContent;
        $this->aliasContent = $aliasContent;
    }
    
    /**
     * @return string
     */
    public function getCloneContent(): string
    {
        return $this->cloneContent;
    }
    
    /**
     * @param   string  $cloneContent
     *
     * @return ClassOverrideContentFilterEvent
     */
    public function setCloneContent(string $cloneContent): ClassOverrideContentFilterEvent
    {
        $this->cloneContent = $cloneContent;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getAliasContent(): string
    {
        return $this->aliasContent;
    }
    
    /**
     * @param   string  $aliasContent
     *
     * @return ClassOverrideContentFilterEvent
     */
    public function setAliasContent(string $aliasContent): ClassOverrideContentFilterEvent
    {
        $this->aliasContent = $aliasContent;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getClassNameToOverride(): string
    {
        return $this->classNameToOverride;
    }
    
    /**
     * @return string
     */
    public function getCopyClassName(): string
    {
        return $this->copyClassName;
    }
    
    /**
     * @return string
     */
    public function getInitialClassName(): string
    {
        return $this->initialClassName;
    }
    
    /**
     * @return string
     */
    public function getFinalClassName(): string
    {
        return $this->finalClassName;
    }
}
