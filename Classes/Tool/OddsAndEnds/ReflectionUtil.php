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
 * Last modified: 2021.05.10 at 18:57
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\OddsAndEnds;

use Closure;
use InvalidArgumentException;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionObject;

class ReflectionUtil implements NoDiInterface
{
    /**
     * A helper to convert any form of callable into a reflection function object.
     * The $callable be any kind of valid php callable
     *
     * @param $callable
     *
     * @return \ReflectionFunction
     */
    public static function makeReflectionForCallable($callable): ReflectionFunction
    {
        $ref = null;
        if (is_object($callable)) {
            if ($callable instanceof Closure) {
                $ref = new ReflectionFunction($callable);
            } else {
                $ref = new ReflectionObject((object)$callable);
            }
        }
        if ($ref === null && is_string($callable)) {
            if (class_exists($callable)) {
                $ref = new ReflectionClass($callable);
            } else {
                $ref = new ReflectionFunction($callable);
            }
        }
        if ($ref === null && is_array($callable) && count($callable) === 2) {
            if (is_string($callable[0])) {
                $ref = new ReflectionClass($callable[0]);
            } else {
                $ref = new ReflectionObject($callable[0]);
            }
            $ref = $ref->getMethod($callable[1]);
        }
        if ($ref === null) {
            throw new InvalidArgumentException('Could not generate a key for your given callable!');
        }
        
        return $ref;
    }
    
    /**
     * Iterates the call-stack backwards and tries to find the find an object with the $className in it.
     * It will retrieve the first matching result.
     *
     * @param   string    $className  The class name to look up
     * @param   int|null  $limit      Optional limit of call-stack entries to process
     *
     * @return object|null
     */
    public static function getClosestFromStack(string $className, ?int $limit = null): ?object
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit ?? 0) as $call) {
            if (isset($call['object']) && $call['object'] instanceof $className) {
                return $call['object'];
            }
        }
        
        return null;
    }
}
