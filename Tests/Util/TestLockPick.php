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
 * Last modified: 2021.11.26 at 16:50
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tests\Util;


use InvalidArgumentException;
use ReflectionObject;

class TestLockPick
{
    /**
     * @var object The given instance
     */
    protected $instance;
    
    /**
     * @var \ReflectionObject
     */
    protected $reflector;
    
    public function __construct(object $instance)
    {
        $this->reflector = new ReflectionObject($instance);
        
        if (! $this->reflector->isUserDefined()) {
            throw new InvalidArgumentException('You can only pick locks of user defined classes!');
        }
        
        $this->instance = $instance;
    }
    
    /**
     * Access class property
     *
     * @param   string  $name  The name of the property to access
     *
     * @return mixed The property's value
     */
    public function __get($name)
    {
        if (! $this->reflector->hasProperty($name)) {
            if ($this->reflector->hasMethod('__get')) {
                return $this->reflector->getMethod('__get')->invoke($this->instance, $name);
            }
            
            throw new InvalidArgumentException('The property: "' . $name . '" is unknown!');
        }
        
        $prop = $this->reflector->getProperty($name);
        
        if (! $prop->isPublic()) {
            $prop->setAccessible(true);
        }
        
        return $prop->getValue($this->instance);
    }
    
    /**
     * Set a value of a property inside the subject
     *
     * @param   string  $name   The name of the property to set
     * @param   mixed   $value  The value to set for the given property
     *
     */
    public function __set($name, $value)
    {
        if (! $this->reflector->hasProperty($name)) {
            if ($this->reflector->hasMethod('__set')) {
                $this->reflector->getMethod('__set')->invoke($this->instance, $name, $value);
            }
            
            throw new InvalidArgumentException('The property: "' . $name . '" is unknown!');
        }
        
        $prop = $this->reflector->getProperty($name);
        
        if (! $prop->isPublic()) {
            $prop->setAccessible(true);
        }
        
        $prop->setValue($this->instance, $value);
    }
    
    /**
     * Executes a given method of the subject
     *
     * @param   string  $name       The name of the method to execute
     * @param   array   $arguments  The arguments to pass to the method
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (! $this->reflector->hasMethod($name)) {
            if ($this->reflector->hasMethod('__call')) {
                $this->reflector->getMethod('__call')->invokeArgs($this->instance, $arguments);
            }
            
            throw new InvalidArgumentException('The method: "' . $name . '" is unknown!');
        }
        
        $method = $this->reflector->getMethod($name);
        
        if (! $method->isPublic()) {
            $method->setAccessible(true);
        }
        
        return $method->invokeArgs($this->instance, $arguments);
    }
    
    /**
     * Sets the value of a static property
     *
     * @param   string  $className
     * @param   string  $property
     * @param           $value
     */
    public static function setStaticProperty(string $className, string $property, $value): void
    {
        $prop = (new \ReflectionClass($className))->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($value);
    }
    
    /**
     * Returns the value of a static property of a class
     *
     * @param   string  $className
     * @param   string  $property
     *
     * @return mixed
     */
    public static function getStaticProperty(string $className, string $property)
    {
        $prop = (new \ReflectionClass($className))->getProperty($property);
        $prop->setAccessible(true);
        
        return $prop->getValue();
    }
}