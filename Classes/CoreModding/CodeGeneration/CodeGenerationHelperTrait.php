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

namespace LaborDigital\Typo3BetterApi\CoreModding\CodeGeneration;

use ReflectionMethod;

trait CodeGenerationHelperTrait
{
    /**
     * Helper which is used to build the parameter string of a given reflection method,
     * to be dumped back into the source code.
     *
     * @param \ReflectionMethod $method
     *
     * @return string
     */
    protected function generateMethodArgs(ReflectionMethod $method): string
    {
        $args = [];
        foreach ($method->getParameters() as $param) {
            $arg = [];
            
            // Add type definition
            if (method_exists($param, 'hasType') && $param->hasType()) {
                $type = $param->getType()->getName();
                
                // Make sure the type of classes starts with a backslash...
                if (stripos($type, '\\') !== false || class_exists($type)) {
                    $type = '\\' . $type;
                }
                
                // Check for a nullable type
                if (method_exists($param, 'allowsNull') && $param->allowsNull()) {
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
     * @param \ReflectionMethod $method
     *
     * @return string
     */
    protected function generateMethodSignature(ReflectionMethod $method): string
    {
        $args = $this->generateMethodArgs($method);
        
        // Build prefixes
        $prefixes = [];
        if ($method->isAbstract() && !$method->getDeclaringClass()->isInterface()) {
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
            $type = $method->getReturnType();
            $isObjectOrInterface = class_exists($type) || interface_exists($type);
            $returnType = ':' . ($isObjectOrInterface ? '\\' : '') . $type->getName();
        }
        
        // Build signature
        return implode(' ', $prefixes) . ' ' . $method->getName() . '(' . $args . ')' . $returnType;
    }
    
    /**
     * Internal helper to parse a method's php doc block and to convert it into a usable description for our function
     *
     * @param string $desc
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
}
