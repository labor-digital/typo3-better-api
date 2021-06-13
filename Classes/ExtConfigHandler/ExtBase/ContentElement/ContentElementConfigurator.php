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
 * Last modified: 2021.06.11 at 20:10
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\ContentElement;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator;

class ContentElementConfigurator extends AbstractElementConfigurator implements NoDiInterface
{
    
    protected $wizardTab = 'common';
    
    /**
     * The section label of this element when it is rendered in the cType select box
     *
     * @var string
     */
    protected $cTypeSection;
    
    /**
     * Either null if the default content element typo script should be copied from lib.contentElement
     * Alternatively the name of a content object like COA that is will be used instead
     *
     * @var string|null
     */
    protected $contentObject;
    
    /**
     * Either null if the default behaviour is active, or the signature of another content element
     * that should be overwritten with this element
     *
     * @var string|null
     */
    protected $replacementSignature;
    
    /**
     * Returns the currently set section label of this element when it is rendered in the cType select box.
     *
     * @return string
     */
    public function getCTypeSection(): string
    {
        return $this->cTypeSection ?? '';
    }
    
    /**
     * Is used to set the section label of this element when it is rendered in the cType select box.
     * If this is not defined, a label is automatically generated using the extension key
     *
     * @param   string  $cTypeSection
     *
     * @return $this
     */
    public function setCTypeSection(string $cTypeSection): self
    {
        $this->cTypeSection = $cTypeSection;
        
        return $this;
    }
    
    /**
     * This option can be used if you want this element to overwrite another content element, like "text", "header" or
     * "table". This content element's controller will be used to overwrite the default rendering definition.
     *
     * Please note: if you use this option the title, description and wizard configurations are ignored!
     * Because we will replace the existing element and inherit its configuration
     *
     * @param   string|null  $replacementSignature  The CType of the element that should be replaced
     *                                              Sete it to null to disable the replacement
     *
     * @return $this
     */
    public function replaceOtherElement(?string $replacementSignature): self
    {
        $this->replacementSignature = trim($replacementSignature);
        
        return $this;
    }
    
    /**
     * Returns the configured CType of another element to be replaced by this element
     *
     * @return string|null
     * @see replaceOtherElement
     */
    public function getReplacementSignature(): ?string
    {
        return $this->replacementSignature;
    }
    
    /**
     * By default the content element will be rendered with the lib.contentElement default definition.
     * In a normal setup, this means you probably use FLUIDTEMPLATE, you can use this option to
     * switch the content element to something else.
     *
     * As an example: you can set it to "COA" if you don't want the FluidStyledContent wrap around the content element.
     *
     * @param   string  $contentObject
     *
     * @return $this
     */
    public function setContentObject(string $contentObject): self
    {
        $this->contentObject = $contentObject;
        
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getContentObject(): ?string
    {
        return $this->contentObject;
    }
    
    /**
     * @inheritDoc
     */
    protected function getDataHookTableFieldConstraints(): array
    {
        return ['CType' => $this->getSignature()];
    }
}
