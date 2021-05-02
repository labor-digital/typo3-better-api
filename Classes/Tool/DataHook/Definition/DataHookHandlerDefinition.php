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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);

namespace LaborDigital\T3ba\Tool\DataHook\Definition;

class DataHookHandlerDefinition
{
    /**
     * The handler callable to execute
     *
     * @var callable
     */
    public $handler;
    
    /**
     * True if the handler applies to the whole table instead of a specific field
     *
     * @var bool
     */
    public $appliesToTable = false;
    
    /**
     * Either the name of the field or the name of the table this hook applies to
     *
     * @var string
     */
    public $key;
    
    /**
     * The data this handler applies to, this is NULL if the handler applies to the whole table
     *
     * @var mixed
     */
    public $data;
    
    /**
     * The tca configuration of this specific handler
     *
     * @var array
     */
    public $tca;
    
    /**
     * Additional handler options that can be passed as second array in a handler definition.
     * Possible keys are:
     * - constraints (array): A list of fieldName => value constraints that have to be matched in the row
     * in order for the handler to process the record.
     * - contextClass (string): An optional override over the default "DataHookContext" class. The given class
     * MUST extend DataHookContext but can also provide additional functionality.
     *
     * @var array
     */
    public $options;
    
    /**
     * The path through the data array to where this value is stored
     *
     * @var array
     */
    public $path;
}
