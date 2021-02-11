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
 * Last modified: 2020.10.18 at 20:53
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\DataHook\FieldPacker;


use LaborDigital\T3BA\Core\Di\PublicServiceInterface;
use LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition;

interface FieldPackerInterface extends PublicServiceInterface
{
    /**
     * Receives the data hook definition object and must iterate the data in order
     * to deserialize fields from a JSON, xml or some other source.
     *
     * The method MUST modify $definition->data after it deserialized the fields.
     * The method MUST return a sequential array containing the keys of all fields that have been unpacked
     *
     * @param   \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition  $definition
     *
     * @return array
     */
    public function unpackFields(DataHookDefinition $definition): array;

    /**
     * Receives the data hook definition and must do the reverse of unpackFields().
     * It should re-serialize the data for the database, to JSON, xml or some other source.
     *
     * The method MUST modify $definition->data after it serialized the fields.
     * The method MUST only serialize the fields that are given $fieldsToPack, all other
     * fields are kept in their original state and therefore don't need to be re-serialized.
     *
     * @param   \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition  $definition
     * @param   array                                                           $fieldsToPack
     */
    public function packFields(DataHookDefinition $definition, array $fieldsToPack): void;
}
