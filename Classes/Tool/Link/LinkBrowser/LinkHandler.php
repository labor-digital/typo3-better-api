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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Link\LinkBrowser;


use LaborDigital\T3BA\Tool\OddsAndEnds\NamingUtil;
use TYPO3\CMS\Backend\Form\Element\InputLinkElement;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\LinkHandling\RecordLinkHandler;

class LinkHandler extends RecordLinkHandler
{
    protected $baseUrn = 't3://linkSetRecord';
    
    /**
     * Used to generate the preview in the backend input link element node
     *
     * @param   array             $linkData
     * @param   array             $linkParts
     * @param   array             $data
     * @param   InputLinkElement  $element
     *
     * @return array
     * @see InputLinkElement::getLinkExplanation()
     */
    public function getFormData(array $linkData, array $linkParts, array &$data, InputLinkElement $element)
    {
        // Make sure our table, which can be a model name as well, resolves to a table!
        $table = &$data['pageTsConfig']['TCEMAIN.']['linkHandler.'][$linkData['identifier'] . '.']
        ['configuration.']['table'];
        
        if (class_exists($table)) {
            $table = NamingUtil::resolveTableName($table);
        }
        
        // Rewrite the request to a default "record" type
        return InputLinkElementAdapter::extractLinkExplanation($element, str_replace(
            'linkSetRecord?', LinkService::TYPE_RECORD . '?', $linkParts['url']
        ));
    }
}
