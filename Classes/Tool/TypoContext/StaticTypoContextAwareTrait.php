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


namespace LaborDigital\T3ba\Tool\TypoContext;


use Psr\Container\ContainerInterface;

trait StaticTypoContextAwareTrait
{
    /**
     * @var \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     */
    protected static $__typoContext;
    
    /**
     * Injects the typo context instance
     *
     * @param   \LaborDigital\T3ba\Tool\TypoContext\TypoContext  $typoContext
     */
    public static function injectTypoContext(TypoContext $typoContext): void
    {
        static::$__typoContext = $typoContext;
    }
    
    /**
     * Returns the typo context instance
     *
     * @return TypoContext
     */
    protected static function getTypoContext(): TypoContext
    {
        if (isset(static::$__typoContext)) {
            return static::$__typoContext;
        }
        
        if (method_exists(static::class, 'getContainer')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $container = static::getContainer();
            if ($container instanceof ContainerInterface) {
                return static::$__typoContext = $container->get(TypoContext::class);
            }
        }
        
        return static::$__typoContext = TypoContext::getInstance();
    }
    
    /**
     * Returns the typo context instance
     *
     * @return TypoContext
     * @deprecated will be removed in v11, use getTypoContext() instead!
     */
    protected static function TypoContext(): TypoContext
    {
        return static::getTypoContext();
    }
}
