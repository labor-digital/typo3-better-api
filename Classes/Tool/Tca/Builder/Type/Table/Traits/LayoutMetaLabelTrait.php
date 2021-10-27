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
 * Last modified: 2021.10.25 at 09:44
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Traits;


trait LayoutMetaLabelTrait
{
    use LayoutMetaTrait;
    
    abstract protected function getLayoutMetaLabelIdx(): int;
    
    /**
     * @inheritDoc
     *
     * @return $this
     */
    public function setLabel(?string $label)
    {
        $this->layoutMeta[$this->getLayoutMetaLabelIdx()] = $label ?? '';
        
        return parent::setLabel($label);
    }
    
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return $this->label ?? $this->layoutMeta[$this->getLayoutMetaLabelIdx()] ?? '';
    }
    
    /**
     * @inheritDoc
     */
    public function getLayoutMeta(): array
    {
        $meta = $this->layoutMeta;
        $labelIdx = $this->getLayoutMetaLabelIdx();
        $metaLabel = $this->layoutMeta[$labelIdx];
        $configLabel = $this->getLabel();
        
        // If meta.1 (the label) is the same as the one configured -> don't define it in the string
        if (! empty($metaLabel) && $metaLabel === $configLabel) {
            // If the LAST element is the label -> remove it -> otherwise set it to an empty string
            if (count($meta) === $labelIdx + 1) {
                unset($meta[$labelIdx]);
            } else {
                $meta[$labelIdx] = '';
            }
        }
        
        return $meta;
    }
}