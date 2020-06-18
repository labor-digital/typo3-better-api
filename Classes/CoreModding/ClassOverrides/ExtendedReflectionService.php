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
 * Last modified: 2020.03.18 at 19:33
 */

namespace LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides;

use LaborDigital\Typo3BetterApi\Container\LazyConstructorInjection\LazyConstructorInjectionHook;
use LaborDigital\Typo3BetterApi\Event\Events\ClassSchemaFilterEvent;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use TYPO3\CMS\Extbase\Reflection\BetterApiClassOverrideCopy__ReflectionService;
use TYPO3\CMS\Extbase\Reflection\ClassSchema;

class ExtendedReflectionService extends BetterApiClassOverrideCopy__ReflectionService
{
    public function getClassSchema($classNameOrObject): ClassSchema
    {
        $schema = parent::getClassSchema($classNameOrObject);
        
        // Avoid infinite recursion
        if ($classNameOrObject === LazyConstructorInjectionHook::class
            || $classNameOrObject instanceof LazyConstructorInjectionHook) {
            return $schema;
        }
        
        // Allow filtering
        $e = new ClassSchemaFilterEvent($schema, $classNameOrObject);
        TypoEventBus::getInstance()->dispatch($e);
        
        return $e->getSchema();
    }
}
