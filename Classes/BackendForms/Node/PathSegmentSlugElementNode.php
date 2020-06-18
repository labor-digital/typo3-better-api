<?php
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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\Node;

use TYPO3\CMS\Backend\Form\Element\InputSlugElement;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;

/**
 * Class PathSegmentSlugElementNode
 * This node is a small fix for the backend rendering of the slug element
 * if it is used as a path-segment only. It adds a more obvious "dummy" path to the base
 * url to show the editor that his final url will look different than what he sees in the field
 *
 * @package LaborDigital\Typo3BetterApi\BackendForms\Node
 */
class PathSegmentSlugElementNode extends InputSlugElement
{
    /**
     * @inheritDoc
     */
    protected function getPrefix(SiteInterface $site, int $requestLanguageId = 0): string
    {
        $prefix   = parent::getPrefix($site, $requestLanguageId);
        $lastChar = substr(trim(strrev($prefix)), 0, 1);
        if ($lastChar !== '/') {
            return $prefix . '/slug/of/the/page/';
        }
        
        return $prefix;
    }
}
