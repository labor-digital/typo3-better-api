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
 * Last modified: 2021.01.30 at 12:53
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\FieldPreset;

use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext;

/**
 * Interface FieldPresetInterface
 *
 * Defines a class providing field presets.
 *
 * Each preset must be registered as a PUBLIC method.
 * The name of the method MUST start with apply in order to be detected as a preset.
 * The resulting preset name will get the "apply" stripped automatically.
 *
 * As an example
 *
 * YourPresetClass extends AbstractFieldPreset {
 *
 *      // This method is not used as a preset
 *      public function doSomething(){}
 *
 *      // This method IS USED as a preset, because its name starts with "apply" and it is public.
 *      // You can use it on your form fields with $field->applyPreset()->myPreset()
 *      public function applyMyPreset(){}
 * }
 *
 * @package LaborDigital\T3BA\Tool\Tca\Builder\FieldPreset
 * @see     \LaborDigital\T3BA\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset
 */
interface FieldPresetInterface extends PublicServiceInterface
{
    /**
     * This is used to inject the form field the next preset method should refer to
     *
     * @param   AbstractField  $field
     *
     * @return void
     */
    public function setField(AbstractField $field): void;

    /**
     * This is used to inject the tca builder context object for the field that will be configured next
     *
     * @param   TcaBuilderContext  $context
     *
     * @return void
     */
    public function setContext(TcaBuilderContext $context): void;
}
