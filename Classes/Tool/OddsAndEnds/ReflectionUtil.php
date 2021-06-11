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
 * Last modified: 2021.06.09 at 12:40
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\OddsAndEnds;

use Closure;
use InvalidArgumentException;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionObject;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;

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
    
    /**
     * Helper which is used to build the parameter string of a given reflection method,
     * to be dumped back into the source code.
     *
     * @param   \ReflectionMethod  $method
     *
     * @return string
     */
    public static function generateMethodArgs(ReflectionMethod $method): string
    {
        $args = [];
        foreach ($method->getParameters() as $param) {
            $arg = [];
            
            // Add type definition
            if ($param->hasType()) {
                $type = implode('|', static::parseType($param));
                
                // Make sure the type of classes starts with a backslash...
                if (strpos($type, '\\') !== false || class_exists($type)) {
                    $type = '\\' . $type;
                }
                
                // Check for a nullable type
                if ($param->allowsNull()) {
                    $type = '?' . $type;
                }
                
                $arg[] = $type;
            }
            
            // Add name of the arg
            $argName = '$' . $param->getName();
            
            // Check if this argument is used as a reference
            if ($param->isPassedByReference()) {
                $argName = '&' . $argName;
            }
            
            // Add name to argument
            $arg[] = $argName;
            
            // Add possible default value
            if ($param->isDefaultValueAvailable()) {
                $default = '= ';
                
                if ($param->isDefaultValueConstant()) {
                    $default .= $param->getDefaultValueConstantName();
                } else {
                    $default .= str_replace(PHP_EOL, ' ', var_export($param->getDefaultValue(), true));
                }
                $arg[] = $default;
            }
            
            // Implode the single argument
            $args[] = implode(' ', $arg);
        }
        
        // Implode all arguments
        return implode(', ', $args);
    }
    
    /**
     * Helper which is used to build a method signature out of the given method reflection
     *
     * @param   \ReflectionMethod  $method
     *
     * @return string
     */
    public static function generateMethodSignature(ReflectionMethod $method): string
    {
        $args = static::generateMethodArgs($method);
        
        // Build prefixes
        $prefixes = [];
        if ($method->isAbstract() && ! $method->getDeclaringClass()->isInterface()) {
            $prefixes[] = 'abstract';
        }
        if ($method->isFinal()) {
            $prefixes[] = 'final';
        }
        if ($method->isPublic()) {
            $prefixes[] = 'public';
        }
        if ($method->isProtected()) {
            $prefixes[] = 'protected';
        }
        if ($method->isPrivate()) {
            $prefixes[] = 'private';
        }
        if ($method->isStatic()) {
            $prefixes[] = 'static';
        }
        $prefixes[] = ($method->returnsReference() ? '&' : '') . 'function';
        
        // Build return type
        $returnType = '';
        if ($method->hasReturnType()) {
            $type = static::parseType($method);
            if (! empty($type)) {
                $typeName = implode('|', $type);
                $isObjectOrInterface = class_exists($typeName) || interface_exists($typeName);
                $returnType = ':' . ($isObjectOrInterface ? '\\' : '') . $typeName;
            }
        }
        
        // Build signature
        return implode(' ', $prefixes) . ' ' . $method->getName() . '(' . $args . ')' . $returnType;
    }
    
    /**
     * Helper to parse a multitude of data type options into an array of all possible options
     *
     * @param   string|array|object  $typeOrParent
     *
     * @return array|string[]
     */
    public static function parseType($typeOrParent): array
    {
        if (empty($typeOrParent)) {
            return [];
        }
        
        $slashStripper = function ($v) {
            if (is_string($v)) {
                return ltrim($v, '\\');
            }
            
            return $v;
        };
        
        if ($typeOrParent instanceof ReflectionProperty) {
            if (! is_callable([$typeOrParent, 'hasType'])
                || ! is_callable([$typeOrParent, 'getType'])
                || ! $typeOrParent->hasType()) {
                return [];
            }
            
            $rawType = $typeOrParent->getType();
        } elseif ($typeOrParent instanceof ReflectionParameter) {
            if (! $typeOrParent->hasType()) {
                return [];
            }
            
            $rawType = $typeOrParent->getType();
        } elseif ($typeOrParent instanceof ReflectionFunction || $typeOrParent instanceof ReflectionMethod) {
            if (! $typeOrParent->hasReturnType()) {
                return [];
            }
            
            $rawType = $typeOrParent->getReturnType();
        } elseif (is_string($typeOrParent)) {
            return array_map($slashStripper, explode('|', $typeOrParent));
        } elseif (is_array($typeOrParent)) {
            return array_map([static::class, 'parseType'], $typeOrParent);
        } else {
            throw new InvalidArgumentException('Could not parse the type, because ' . SerializerUtil::serializeJson($typeOrParent) . ' is an invalid argument');
        }
        
        if ($rawType === null) {
            return [];
        }
        
        $result = [];
        foreach ($rawType instanceof ReflectionUnionType ? $rawType->getTypes() : [$rawType] as $type) {
            $typeName = $type instanceof ReflectionNamedType ? $type->getName() : (string)$type;
            
            if ($type->isBuiltin()) {
                $result[] = $typeName;
                continue;
            }
            
            $lcTypeName = strtolower($typeName);
            
            if ('self' !== $lcTypeName && 'parent' !== $lcTypeName) {
                $result[] = $typeName;
                continue;
            }
            
            if (! $typeOrParent instanceof ReflectionFunction && ! $typeOrParent instanceof ReflectionMethod) {
                continue;
            }
            
            if ('self' === $lcTypeName) {
                $result[] = $typeOrParent->getDeclaringClass()->name;
            } else {
                $result[] = ($parent = $typeOrParent->getDeclaringClass()->getParentClass()) ? $parent->name : null;
            }
        }
        
        return array_map($slashStripper, $result);
    }
    
    
    /**
     * Helper to parse a method's php doc block and to convert it into a usable description for other function
     *
     * @param   string|false  $desc  The doc block content to be parsed
     *
     * @return array The result of lines inside the doc block
     */
    public static function sanitizeDesc($desc): array
    {
        if (! is_string($desc)) {
            return [];
        }
        
        $lines = preg_split("/\r?\n/", $desc);
        $linesFiltered = [];
        foreach ($lines as $line) {
            if (stripos($line, '@package') !== false || stripos($line, '@return') !== false) {
                continue;
            }
            
            if (stripos($line, '* Class ') !== false) {
                continue;
            }
            
            $linesFiltered[] = preg_replace('/\s*[\\/*]+\s?/', '', $line);
        }
        
        // Only keep a single empty line, drop all others
        $lines = $linesFiltered;
        $linesFiltered = [];
        // We start with true, to make sure the first line (which should always be empty) is removed
        $lastWasEmpty = true;
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                if ($lastWasEmpty) {
                    continue;
                }
                
                $lastWasEmpty = true;
            } else {
                $lastWasEmpty = false;
            }
            
            $linesFiltered[] = $line;
        }
        
        return $linesFiltered;
    }
    
}
