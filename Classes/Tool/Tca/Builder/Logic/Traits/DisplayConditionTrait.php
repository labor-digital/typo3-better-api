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
/**
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
 * Last modified: 2020.03.19 at 02:52
 */

namespace LaborDigital\T3ba\Tool\Tca\Builder\Logic\Traits;

use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException;

trait DisplayConditionTrait
{
    
    /**
     * Sets the display condition for the current column
     *
     * Special feature: If you give an array like ["fieldName", "=" , "0"], the logic will automatically
     * convert it into the internal format like: "FIELD:fieldName:=:0"
     *
     * Auto-And: If you apply multiple arrays like [["fieldName", "=" , "0"],["fieldName", "=" , "2"]]
     * the values will be combined using the "AND" conditional
     *
     * NULL: If the value is set to null, the current display condition will be removed
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Columns/Index.html#displaycond
     *
     * @param   string|array|null  $condition
     *
     * @return $this
     * @throws \LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException
     */
    public function setDisplayCondition($condition)
    {
        if (empty($condition)) {
            if ($condition === null) {
                $this->config['displayCond'] = null;
            }
            
            return $this;
        }
        
        if (is_string($condition)) {
            $this->config['displayCond'] = $condition;
            
            return $this;
        }
        
        if (is_array($condition)) {
            $this->config['displayCond']
                = $this->getRoot()->getContext()->cs()->displayCondBuilder->build($this, $condition);
            
            return $this;
        }
        
        throw new TcaBuilderException('Only strings and arrays are allowed as display conditions!');
    }
    
    /**
     * Returns the currently configured display condition, or an empty string if there is none
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Columns/Index.html#displaycond
     *
     * @return array|string
     */
    public function getDisplayCondition()
    {
        return $this->config['displayCond'] ?? '';
    }
}
