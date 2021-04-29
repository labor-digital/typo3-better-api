<?php
/*
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
 * Last modified: 2020.08.22 at 23:13
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Tool\OddsAndEnds;

use InvalidArgumentException;

trait InternalAccessTrait
{
    /**
     * Lets you access any value of this object, even if it is protected or private
     *
     * @param   string  $key
     *
     * @return mixed
     */
    public function getProperty(string $key)
    {
        if (! $this->hasProperty($key)) {
            throw new InvalidArgumentException(
                'The object ' . get_class($this->getExecutionTarget())
                . ' does not have a property, called: "' . $key . '"!');
        }
        
        return $this->getExecutionTarget()->$key;
    }
    
    /**
     * Returns true if this object has a property with name $key.
     *
     * @param   string  $key
     *
     * @return bool
     */
    public function hasProperty(string $key): bool
    {
        return property_exists($this->getExecutionTarget(), $key);
    }
    
    /**
     * Sets a given property key with the given value
     *
     * @param   string  $key
     * @param   mixed   $value
     *
     * @return $this
     */
    public function setProperty(string $key, $value): self
    {
        if (! $this->hasProperty($key)) {
            throw new InvalidArgumentException(
                'The object ' . get_class($this->getExecutionTarget())
                . ' does not have a property, called: "' . $key . '"!');
        }
        $this->getExecutionTarget()->$key = $value;
        
        return $this;
    }
    
    /**
     * Can be used to call any method inside the current object, even if it is protected or private
     *
     * @param   string  $method
     * @param   array   $args  Method arguments as an array
     *
     * @return mixed
     */
    public function callMethod(string $method, array $args = [])
    {
        if (! $this->hasMethod($method)) {
            throw new InvalidArgumentException(
                'The object ' . get_class($this->getExecutionTarget())
                . ' does not have a method, called: "' . $method . '"!');
        }
        
        return call_user_func_array([$this->getExecutionTarget(), $method], $args);
    }
    
    /**
     * Returns true if the object has a method with the name of $method.
     *
     * @param   string  $method
     *
     * @return bool
     */
    public function hasMethod(string $method): bool
    {
        return method_exists($this->getExecutionTarget(), $method);
    }
    
    /**
     * This method should return the object we should use as target.
     * By default you can just do return $this. However if you are running inside a proxy element
     * you might want to return another property, where your real instance is stored.
     *
     * @return mixed
     */
    abstract public function getExecutionTarget();
}
