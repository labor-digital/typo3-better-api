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
 * Last modified: 2021.04.20 at 11:02
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\ExtBase\ContentElement;


use LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator;

class ContentElementConfigurator extends AbstractElementConfigurator
{

    protected $wizardTab = 'common';

    /**
     * The section label of this element when it is rendered in the cType select box
     *
     * @var string
     */
    protected $cTypeSection;

    /**
     * Returns the currently set section label of this element when it is rendered in the cType select box.
     *
     * @return string
     */
    public function getCTypeSection(): string
    {
        return (string)$this->cTypeSection;
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
     * @inheritDoc
     */
    protected function getDataHookTableFieldConstraints(): array
    {
        return ['CType' => $this->getSignature()];
    }
}
