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


namespace LaborDigital\T3ba\ExtConfigHandler\Scheduler\Task;


use LaborDigital\T3ba\Core\Di\NoDiInterface;

class TaskConfigurator implements NoDiInterface
{
    
    /**
     * The name of the task class
     *
     * @var string
     */
    protected $className;
    
    /**
     * A speaking title for the task
     *
     * @var string
     */
    protected $title;
    
    /**
     * An optional description for your task
     *
     * @var string
     */
    protected $description;
    
    /**
     * Additional configuration options as you would normally define them in the typo3 array.
     *
     * @var array
     */
    protected $options = [];
    
    /**
     * TaskConfigurator constructor.
     *
     * @param   string  $title
     * @param   string  $className
     */
    public function __construct(string $title, string $className)
    {
        $this->title = $title;
        $this->className = $className;
    }
    
    /**
     * Returns either the set speaking title for the task, or a automatically generated one
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
    
    /**
     * Allows you to define a speaking title for the task
     *
     * @param   string  $title
     *
     * @return TaskConfigurator
     */
    public function setTitle(string $title): TaskConfigurator
    {
        $this->title = $title;
        
        return $this;
    }
    
    /**
     * Returns an optional description for your task, or null
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    /**
     * @param   string  $description
     *
     * @return TaskConfigurator
     */
    public function setDescription(string $description): TaskConfigurator
    {
        $this->description = $description;
        
        return $this;
    }
    
    /**
     * Returns additional configuration options as you would normally define them in the typo3 array
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
    
    /**
     * Allows you to set additional configuration options as you would normally define them in the typo3 array
     *
     * @param   array  $options
     *
     * @return TaskConfigurator
     */
    public function setOptions(array $options): TaskConfigurator
    {
        $this->options = $options;
        
        return $this;
    }
}
