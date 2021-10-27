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


namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\Table;


use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractContainer;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Traits\LayoutMetaLabelTrait;

class TcaPalette extends AbstractContainer
{
    use LayoutMetaLabelTrait {
        getLayoutMeta as getLayoutMetaRoot;
    }
    
    /**
     * If set to true, this palette will never be shown, but the fields of the palette are technically
     * rendered as hidden elements in FormEngine.
     *
     * @var bool
     */
    protected $hidden = false;
    
    /**
     * New in version 11.3: The palettes description property has been added with TYPO3 11.3.
     * Allows to display a localized description text into the palette declaration. It will be displayed below the palette label.
     * This additional help text can be used to clarify some field usages directly in the UI.
     *
     * @todo implement getters and setters in v11
     *
     * @var string|null
     */
    protected $description;
    
    protected function getLayoutMetaLabelIdx(): int
    {
        return 0;
    }
    
    /**
     * If true, this palette will never be shown, but the fields of the palette are technically
     * rendered as hidden elements in FormEngine
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }
    
    /**
     * Defines if the palette should be hidden, meaning the palette will never be shown,
     * but the fields of the palette are rendered as hidden elements
     *
     * @param   bool  $hidden
     */
    public function setHidden(bool $hidden = true): void
    {
        $this->hidden = $hidden;
    }
    
    /**
     * @inheritDoc
     */
    public function setRaw(array $raw)
    {
        if (isset($raw['isHiddenPalette'])) {
            $this->setHidden((bool)$raw['isHiddenPalette']);
            unset($raw['isHiddenPalette']);
        }
        
        // @todo implement "description" reader in v11
//            if(is_string($config['description'])){
//                $i->setDescription($config['description']);
//            }
        
        return parent::setRaw($raw);
    }
    
    
    /**
     * @inheritDoc
     */
    public function getRaw(): array
    {
        $raw = $this->config;
        
        if ($this->isHidden()) {
            $raw['isHiddenPalette'] = true;
        }
        
        return $raw;
    }
    
    /**
     * @inheritDoc
     */
    public function getLayoutMeta(): array
    {
        $meta = $this->getLayoutMetaRoot();
        $meta[1] = substr($this->getId(), 1);
        
        return $meta;
    }
}
