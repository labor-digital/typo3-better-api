<?php
/*
 * Copyright 2022 LABOR.digital
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
 * Last modified: 2022.01.31 at 19:48
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Rendering\Renderer\FieldRenderer;


use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3ba\Tool\Translation\Translator;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use Throwable;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;

class LinkFieldRenderer implements SingletonInterface
{
    /**
     * @var \TYPO3\CMS\Core\LinkHandling\LinkService
     */
    protected LinkService $linkService;
    
    /**
     * @var \TYPO3\CMS\Frontend\Service\TypoLinkCodecService
     */
    protected TypoLinkCodecService $codecService;
    
    /**
     * @var \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     */
    protected TypoContext $typoContext;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Translation\Translator
     */
    protected Translator $translator;
    
    public function __construct(
        LinkService $linkService,
        TypoLinkCodecService $codecService,
        TypoContext $typoContext,
        Translator $translator
    )
    {
        $this->linkService = $linkService;
        $this->codecService = $codecService;
        $this->typoContext = $typoContext;
        $this->translator = $translator;
    }
    
    /**
     * Renders a typo link value as somewhat readable link
     *
     * @param   string  $value
     *
     * @return string
     */
    public function render(string $value): string
    {
        try {
            $linkData = $this->linkService->resolve($this->codecService->decode($value)['url'] ?? '');
        } catch (Throwable $exception) {
            return $value;
        }
        
        if (empty($linkData['type'])) {
            return $value;
        }
        
        switch ($linkData['type']) {
            case LinkService::TYPE_PAGE:
                $record = BackendUtility::readPageAccess($linkData['pageuid'], '1=1');
                if (! empty($record['uid'])) {
                    return $record['_thePathFull'] . '[' . $record['uid'] . ']';
                }
                
                return $value;
            case LinkService::TYPE_EMAIL:
                return $linkData['email'] ?? $value;
            case LinkService::TYPE_URL:
                return $linkData['url'] ?? $value;
            case LinkService::TYPE_FILE:
                if (! empty($linkData['file'])) {
                    return $linkData['file']->getNameWithoutExtension() . ' [' . $linkData['file']->getUid() . ']';
                }
                
                return $value;
            case LinkService::TYPE_FOLDER:
                if (! empty($linkData['folder'])) {
                    return $linkData['folder']->getPublicUrl();
                }
                
                return $value;
            case LinkService::TYPE_RECORD:
            case 'linkSetRecord':
                $tableName = $this->typoContext->config()->getTsConfigValue([
                    'TCEMAIN',
                    'linkHandler',
                    $linkData['identifier'] ?? '',
                    'configuration',
                    'table',
                ]);
                
                if (! empty($tableName)) {
                    $tableName = NamingUtil::resolveTableName($tableName);
                    $record = BackendUtility::getRecord($tableName, $linkData['uid']);
                    if ($record) {
                        $recordTitle = BackendUtility::getRecordTitle($tableName, $record);
                        $tableTitle = $this->translator->translate($GLOBALS['TCA'][$tableName]['ctrl']['title'] ??
                                                                   $tableName);
                        
                        return sprintf('%s [%s:%d]', $recordTitle, $tableTitle, $linkData['uid']);
                    }
                }
                
                return $value;
            case LinkService::TYPE_TELEPHONE:
                return $linkData['telephone'] ?? $value;
        }
        
        return $value;
    }
}