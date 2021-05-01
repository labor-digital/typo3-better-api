<?php
declare(strict_types=1);
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

namespace LaborDigital\T3BA\Tool\FormEngine\Custom\Field;

use LaborDigital\T3BA\Tool\DataHook\DataHookContext;
use Neunerlei\Arrays\Arrays;

class CustomFieldDataHookContext extends DataHookContext
{
    /**
     * Returns the list of additional options that were passed when the field
     * was applied using the fieldPreset applier.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->handlerDefinition->tca['config']['t3ba'] ?? [];
    }
    
    /**
     * Can be used to return a single option, or returns the default value
     *
     * @param   array|string  $path     The key, or the path to look up
     * @param   null          $default  An optional default value to return if the key/path was not found in the options
     *                                  array
     *
     * @return array|mixed|null
     */
    public function getOption($path, $default = null)
    {
        return Arrays::getPath($this->getOptions(), $path, $default);
    }
    
    /**
     * Returns the registered class of the registered custom-element for this field.
     * If this returns an empty string, the space-time-continuum will explode in around 30 seconds...
     *
     * @return string
     */
    public function getElementClass(): string
    {
        return $this->handlerDefinition->tca['config']['t3baClass'] ?? '';
    }
    
    /**
     * Alias of getKey() to make sure we use the same naming in both contexts
     *
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->getKey();
    }
}
