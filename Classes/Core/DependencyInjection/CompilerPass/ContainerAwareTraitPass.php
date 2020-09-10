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
 * Last modified: 2020.09.09 at 20:21
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\DependencyInjection\CompilerPass;


use LaborDigital\T3BA\Core\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass;
use Symfony\Component\DependencyInjection\Definition;

class ContainerAwareTraitPass extends AbstractRecursivePass
{
    /**
     * @inheritDoc
     */
    protected function processValue($value, bool $isRoot = false)
    {
        $value = parent::processValue($value, $isRoot);

        if (! $value instanceof Definition || ! $value->isAutowired() || $value->isAbstract() || ! $value->getClass()) {
            return $value;
        }
        if (! $reflectionClass = $this->container->getReflectionClass($value->getClass(), false)) {
            return $value;
        }
        if (! in_array(ContainerAwareTrait::class, $reflectionClass->getTraitNames(), true)) {
            return $value;
        }

        foreach ($value->getMethodCalls() as [$method]) {
            if ($method === 'setContainer') {
                return $value;
            }
        }

        $value->addMethodCall('setContainer');

        return $value;
    }

}
