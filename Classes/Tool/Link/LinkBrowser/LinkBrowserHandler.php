<?php
/*
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
 * Last modified: 2020.10.05 at 21:57
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Link\LinkBrowser;


use LaborDigital\T3BA\Tool\OddsAndEnds\NamingUtil;
use TYPO3\CMS\Recordlist\Controller\AbstractLinkBrowserController;
use TYPO3\CMS\Recordlist\LinkHandler\RecordLinkHandler;

class LinkBrowserHandler extends RecordLinkHandler
{
    /**
     * @inheritDoc
     */
    public function initialize(AbstractLinkBrowserController $linkBrowser, $identifier, array $configuration)
    {
        parent::initialize($linkBrowser, $identifier, $configuration);
        
        if (isset($this->configuration['table']) && class_exists($this->configuration['table'])) {
            $this->configuration['table'] = NamingUtil::resolveTableName($this->configuration['table']);
        }
    }
    
    /**
     * @inheritDoc
     */
    public function getBodyTagAttributes(): array
    {
        $attr = parent::getBodyTagAttributes();
        $attr['data-identifier'] = str_replace('t3://record?', 't3://linkSetRecord?', (string)$attr['data-identifier']);
        
        return $attr;
    }
}
