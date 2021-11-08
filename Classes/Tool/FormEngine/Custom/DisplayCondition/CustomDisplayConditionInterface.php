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
 * Last modified: 2021.11.08 at 19:56
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\FormEngine\Custom\DisplayCondition;


use LaborDigital\T3ba\Core\Di\NoDiInterface;

interface CustomDisplayConditionInterface extends NoDiInterface
{
    /**
     * MUST evaluate the display condition based on the parameters.
     *
     * The following arguments are passed as array to the userFunc:
     * - record: the currently edited record
     * - flexContext: details about the FlexForm if the condition is used in one
     * - flexformValueKey: vDEF
     * - conditionParameters: additional parameters
     * The called method is expected to return a bool value: true if the field should be displayed, false otherwise.
     *
     * @param   array  $params
     *
     * @return bool
     */
    public function evaluate(array $params): bool;
}