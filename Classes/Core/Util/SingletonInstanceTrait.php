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


namespace LaborDigital\T3ba\Core\Util;


use LaborDigital\T3ba\Core\Exception\SingletonNotSetException;

trait SingletonInstanceTrait
{
    /**
     * @var self
     */
    protected static $instance;
    
    /**
     * Returns true if the singleton instance for this class was set, false if not.
     *
     * @return bool
     * @todo rename to hasSingleton() and deprecate it in v11
     */
    public static function hasInstance(): bool
    {
        return isset(static::$instance);
    }
    
    /**
     * Returns the singleton instance for the class using this trait
     *
     * @return self
     * @throws \LaborDigital\T3ba\Core\Exception\SingletonNotSetException
     * @todo rename to getSingleton() and deprecate it in v11
     */
    public static function getInstance(): self
    {
        if (empty(static::$instance)) {
            throw new SingletonNotSetException('The singleton instance was not injected using setInstance()');
        }
        
        return static::$instance;
    }
    
    /**
     * Internal helper to inject the instance into the class using this trait
     *
     * @param   self  $instance
     *
     * @return $this
     * @internal
     * @todo rename to setSingleton() and deprecate it in v11
     */
    public static function setInstance(self $instance): self
    {
        static::$instance = $instance;
        
        return $instance;
    }
}
