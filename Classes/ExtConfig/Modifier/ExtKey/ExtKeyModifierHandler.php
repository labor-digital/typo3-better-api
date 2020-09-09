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
 * Last modified: 2020.09.09 at 01:03
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig\Modifier\ExtKey;


use Neunerlei\Configuration\Modifier\AbstractConfigModifier;
use Neunerlei\Configuration\Modifier\ModifierContext;

class ExtKeyModifierHandler extends AbstractConfigModifier
{

    /**
     * @inheritDoc
     */
    public function apply(ModifierContext $context): void
    {
        $classes = $this->findClassesWithInterface($context->getConfigClasses(), ModifyExtKeyInterface::class);
        if (empty($classes)) {
            return;
        }

        foreach ($classes as $class) {
            /** @var string $class */
            $extKey = call_user_func([$class, 'getExtKey']);
            dbge($extKey, $context);
        }
        dbge($classes);
    }

}
