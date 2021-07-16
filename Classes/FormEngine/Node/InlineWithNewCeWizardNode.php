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
 * Last modified: 2021.07.09 at 19:53
 */

declare(strict_types=1);

namespace LaborDigital\T3ba\FormEngine\Node;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\FormEngine\UserFunc\InlineContentElementWizardDataProvider;
use TYPO3\CMS\Backend\Form\Container\InlineControlContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class InlineWithNewCeWizardNode extends InlineControlContainer
{
    use ContainerAwareTrait;
    
    /**
     * @inheritDoc
     */
    public function render()
    {
        $resultArray = parent::render();
        
        $resultArray = $this->rewriteHtml($resultArray);
        $resultArray = $this->rewriteJs($resultArray);
        
        return $resultArray;
    }
    
    /**
     * Rewrites the HTML of the "new" buttons to open the new content element wizard instead
     *
     * @param   array  $resultArray
     *
     * @return array
     */
    protected function rewriteHtml(array $resultArray): array
    {
        $resultArray['html'] = preg_replace(
            '~<button[^>]*?t3js-create-new-button"([^>]*?)>((.|\n)*?)<\/button>~si',
            '<a href="' . $this->makeWizardUrl() .
            '" class="btn btn-default t3js-toggle-new-content-element-wizard"$1>$2</a>',
            $resultArray['html']);
        
        return $resultArray;
    }
    
    /**
     * Creates the url that renders the new content element wizard
     *
     * @return string
     */
    protected function makeWizardUrl(): string
    {
        if (is_int($this->data['databaseRow']['pid'] ?? null) && $this->data['databaseRow']['pid'] > 0) {
            $pid = $this->data['databaseRow']['pid'];
        } elseif (is_int($this->data['parentPageRow']['uid'] ?? null)) {
            $pid = $this->data['parentPageRow']['uid'];
        } else {
            $pid = $this->cs()->typoContext->pid()->getCurrent();
        }
        
        $languageId = $this->data['databaseRow']['sys_language_uid'] ??
                      $this->data['parentPageRow']['sys_language_uid'] ?? 0;
        
        // Allow overrides from the outside
        $colPos = $this->data['parameterArray']['fieldConf']['config']['inline']['colPos'] ?? -88;
        
        $urlParameters = [
            'id' => $pid,
            'sys_language_uid' => is_array($languageId) ? reset($languageId) : $languageId,
            'colPos' => $colPos,
            'uid_pid' => $pid,
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ];
        
        $routeName = $this->cs()->typoContext->config()->getTsConfigValue(
            ['mod', 'newContentElementWizard', 'override'], 'new_content_element_wizard');
        
        return $this->cs()->links->getBackendLink($routeName, ['args' => $urlParameters]);
    }
    
    /**
     * Extends the default behaviour of the inline control container by our internal bridge js
     *
     * @param   array  $resultArray
     *
     * @return array
     */
    protected function rewriteJs(array $resultArray): array
    {
        $last = array_pop($resultArray['requireJsModules']);
        if (! is_array($last)) {
            $resultArray['html'] = 'Error rendering inline content element wizard! JS outdated!';
            
            return $resultArray;
        }
        preg_match('~InlineControlContainer\(\'(.*?)\'\)~', reset($last), $m);
        $containerName = $m[1];
        
        $resultArray['requireJsModules'][] = [
            'TYPO3/CMS/T3ba/Backend/InlineWithNewCeWizard' =>
                'function(bridge){ bridge("' . $containerName . '", "' .
                InlineContentElementWizardDataProvider::AJAX_TARGET . '"); }',
        ];
        
        return $resultArray;
    }
}