<?php
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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\Session;

interface SessionInterface
{
    /**
     * Returns true if the given path exists in the current session
     *
     * @param string $path An Arrays::getPath() compatible selector path
     *
     * @return bool
     */
    public function has(string $path): bool;
    
    /**
     * Returns either the value for the given path or null, if it does not exist
     * Will return the whole session data if null is given as path
     *
     * @param string|null $path    An Arrays::getPath() compatible selector path
     * @param null|mixed  $default An optional default value to be returned if the value does not exist
     *
     * @return mixed|null
     */
    public function get(string $path = null, $default = null);
    
    /**
     * Sets the given value for the path
     *
     * @param string $path  An Arrays::getPath() compatible selector path
     * @param mixed  $value The value to set for the path
     *
     * @return $this
     */
    public function set(string $path, $value);
    
    /**
     * Removes a given path from the session.
     *
     * @param string $path An Arrays::getPath() compatible selector path
     *
     * @return $this
     */
    public function remove(string $path);
}
