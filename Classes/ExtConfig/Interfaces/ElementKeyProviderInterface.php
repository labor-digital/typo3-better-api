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
 * Last modified: 2020.09.09 at 01:11
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig\Interfaces;


interface ElementKeyProviderInterface
{
    /**
     * Must return the key for an element, this can be the plugin key,
     * the module key, or some other key that makes sense based on the current configuration.
     *
     * @return string
     */
    public static function getElementKey(): string;
}
