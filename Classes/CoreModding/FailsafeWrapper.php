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
 * Last modified: 2020.03.19 at 02:35
 */

namespace LaborDigital\Typo3BetterApi\CoreModding;

use Throwable;

/**
 * Class FailsafeWrapper
 *
 * This wrapper is used to allow failsafe execution of the core system
 * without us breaking the code with exceptions...
 *
 * @package LaborDigital\Typo3BetterApi\CoreModding
 */
class FailsafeWrapper
{
    public static $isFailsafe = false;
    
    /**
     * Executes the code, catches all exceptions and returns null if the executed code failed.
     *
     * @param   callable  $handler
     * @param   array     $args
     *
     * @return mixed|null
     */
    public static function handle(callable $handler, array $args = [])
    {
        if (static::$isFailsafe) {
            try {
                return call_user_func_array($handler, $args);
            } catch (Throwable $e) {
                return null;
            }
        }
        
        return call_user_func_array($handler, $args);
    }
}
