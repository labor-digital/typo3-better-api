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
 * Last modified: 2020.10.18 at 20:20
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\DataHook\Definition;


use LaborDigital\T3BA\Tool\DataHook\FieldPacker\FieldPackerInterface;

class DataHookDefinition
{
    /**
     * One of DataHookTypes::TYPE_ that defines which kind of hook is processed
     *
     * @var string
     * @see \LaborDigital\T3BA\Tool\DataHook\DataHookTypes
     */
    public $type;
    
    /**
     * The name of the database table the data corresponds to
     *
     * @var string
     */
    public $tableName;
    
    /**
     * The TCA definition of the table this hook applies to
     *
     * @var array
     */
    public $tca;
    
    /**
     * The prepared data array, with all flex form fields unpacked
     *
     * @var array
     */
    public $data;
    
    /**
     * The raw data which was passed to the hook dispatcher, without anything modified
     *
     * @var array
     */
    public $dataRaw;
    
    /**
     * The list of dirty fields, meaning fields that have been modified by a hook handler
     *
     * @var array
     */
    public $dirtyFields = [];
    
    /**
     * The list of used field packer instances.
     *
     * @var FieldPackerInterface[]
     */
    public $fieldPackers = [];
    
    /**
     * The list of fields that have been unpacked by the used field packer instances.
     * The index of this array matches the index of the used field packer in $fieldPackers.
     *
     * @var array
     */
    public $unpackedFields = [];
    
    /**
     * The name of the context class that should be passed through the registered handlers
     *
     * @var string
     */
    public $contextClass = '';
    
    /**
     * The list of resolved handler definitions for this table
     *
     * @var DataHookHandlerDefinition[]
     */
    public $handlers = [];
    
    /**
     * The given callback will be executed if the data was modified while the hooks were executed
     *
     * @param   callable  $callback  A callback to execute if the data was modified by one of the registered
     *                               hook handlers. It will receive two parameters. The first is the
     *                               data with re-packed flex form fields ready to be used in a db query.
     *                               The second parameter is this object for ease of usage.
     *
     * @return $this
     */
    public function runIfDirty(callable $callback): self
    {
        if (empty($this->dirtyFields)) {
            return $this;
        }
        
        // Pack unpacked data back if required
        $clone = clone $this;
        foreach ($clone->unpackedFields as $id => $fields) {
            $fieldsToPack = [];
            foreach ($fields as $field) {
                if (in_array($field, $clone->dirtyFields, true)) {
                    // Mark the field to be packed
                    $fieldsToPack[] = $field;
                } else {
                    // Restore the data from the stored raw value -> no packing required
                    $clone->data[$field] = $clone->dataRaw[$field];
                }
            }
            
            if (empty($fieldsToPack)) {
                continue;
            }
            
            $clone->fieldPackers[$id]->packFields($clone, $fieldsToPack);
        }
        
        $callback($clone->data, $clone);
        
        return $this;
    }
    
}
