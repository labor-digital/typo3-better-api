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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\Logic\Traits;


use Neunerlei\Arrays\Arrays;

trait ElementConfigTrait
{
    /**
     * The raw configuration as an array
     *
     * @var array
     */
    protected $config = [];
    
    /**
     * Can be used to set raw config values, that are not implemented in the TCA builder facade.
     *
     * @param   array  $raw  The new configuration to be set for this element
     *
     * @return $this
     */
    public function setRaw(array $raw)
    {
        $this->config = $raw;
        
        return $this;
    }
    
    /**
     * Similar to setRaw() but will merge the given array of key/value pairs instead of
     * overwriting the original configuration.
     *
     * This method supports TYPO3's syntax of removing values from the current config if __UNSET is set as key
     *
     * @param   array  $raw  The new configuration to be merged into the config of this element
     *
     * @return $this
     */
    public function mergeRaw(array $raw)
    {
        return $this->setRaw(Arrays::merge($this->config, $raw, 'allowRemoval'));
    }
    
    /**
     * Returns the raw configuration array for this object
     *
     * @return array
     */
    public function getRaw(): array
    {
        return $this->config;
    }
}
