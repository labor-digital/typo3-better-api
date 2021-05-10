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
 * Last modified: 2021.05.10 at 11:43
 */

declare(strict_types=1);
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

namespace LaborDigital\T3ba\Core\CodeGeneration;

use InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

trait CodeGenerationHelperTrait
{
    /**
     * Helper which is used to build the parameter string of a given reflection method,
     * to be dumped back into the source code.
     *
     * @param   \ReflectionMethod  $method
     *
     * @return string
     */
    protected function generateMethodArgs(ReflectionMethod $method): string
    {
        $args = [];
        foreach ($method->getParameters() as $param) {
            $arg = [];
            
            // Add type definition
            if ($param->hasType()) {
                $type = implode('|', $this->parseType($param));
                
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
    protected function generateMethodSignature(ReflectionMethod $method): string
    {
        $args = $this->generateMethodArgs($method);
        
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
            $type = $this->parseType($method);
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
     * Internal helper to parse a method's php doc block and to convert it into a usable description for our function
     *
     * @param   string  $desc
     *
     * @return string
     */
    protected function sanitizeDesc(string $desc): string
    {
        $lines = preg_split("/\r?\n/", $desc);
        $linesFiltered = [];
        foreach ($lines as $line) {
            if (stripos($line, '@package') !== false) {
                continue;
            }
            if (stripos($line, '* Class ') !== false) {
                continue;
            }
            if (stripos($line, '@return') !== false) {
                break;
            }
            $linesFiltered[] = preg_replace('/\\s*[\\/*]+\\s?/', '', $line);
        }
        
        return implode(PHP_EOL . '	 * ', array_filter($linesFiltered));
    }
    
    /**
     * Helper to parse a multitude of data type options into an array of all possible options
     *
     * @param   string|array|object  $typeOrParent
     *
     * @return array|string[]
     */
    protected function parseType($typeOrParent): array
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
        
        if ($typeOrParent instanceof ReflectionParameter) {
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
            return array_map([$this, 'parseType'], $typeOrParent);
        } else {
            throw new InvalidArgumentException('Could not parse the type, because $type is an invalid argument');
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
}
