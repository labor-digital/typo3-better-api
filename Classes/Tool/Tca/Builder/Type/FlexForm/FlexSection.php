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


namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm;


use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractContainer;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\Traits\DisplayConditionTrait;
use Neunerlei\Inflection\Inflector;

class FlexSection extends AbstractContainer
{
    use DisplayConditionTrait;
    
    /**
     * Holds the id of the sections inner container element
     *
     * @var string
     */
    protected $containerItemId = 'item';
    
    /**
     * Holds the section's inner container element's label
     *
     * @var string
     */
    protected $containerItemLabel;
    
    /**
     * Returns the id of the section's inner container element
     *
     * @return string
     */
    public function getContainerItemId(): string
    {
        return $this->containerItemId;
    }
    
    /**
     * Sets the id of the section's inner container element
     *
     * @param   string  $containerItemId
     *
     * @return $this
     */
    public function setContainerItemId(string $containerItemId): self
    {
        $this->containerItemId = $containerItemId;
        
        return $this;
    }
    
    /**
     * Returns the section's inner container element's label
     *
     * @return string
     */
    public function getContainerItemLabel(): string
    {
        return empty($this->containerItemLabel) ? Inflector::toHuman($this->getContainerItemId())
            : $this->containerItemLabel;
    }
    
    /**
     * Sets the section's inner container element's label
     *
     * @param   string  $containerItemLabel
     *
     * @return FlexSection
     */
    public function setContainerItemLabel(string $containerItemLabel): FlexSection
    {
        $this->containerItemLabel = $containerItemLabel;
        
        return $this;
    }
    
}
