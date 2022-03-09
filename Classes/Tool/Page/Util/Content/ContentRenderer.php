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
 * Last modified: 2022.03.09 at 19:46
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Page\Util\Content;


use LaborDigital\T3ba\Tool\Simulation\EnvironmentSimulator;
use LaborDigital\T3ba\Tool\TypoScript\TypoScriptService;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class ContentRenderer implements SingletonInterface
{
    /**
     * @var \LaborDigital\T3ba\Tool\Simulation\EnvironmentSimulator
     */
    protected EnvironmentSimulator $simulator;
    
    /**
     * @var \LaborDigital\T3ba\Tool\TypoScript\TypoScriptService
     */
    protected TypoScriptService $typoScriptService;
    
    public function __construct(EnvironmentSimulator $simulator, TypoScriptService $typoScriptService)
    {
        $this->simulator = $simulator;
        $this->typoScriptService = $typoScriptService;
    }
    
    /**
     * Internal implementation detail of PageService::renderPageContents()
     *
     * @param   int    $pageId
     * @param   array  $options
     *
     * @return string
     */
    public function render(int $pageId, array $options = []): string
    {
        $options = $this->prepareOptions($options);
        
        return $this->simulator->runWithEnvironment([
            'site' => $options['site'],
            'asAdmin' => $options['force'],
            'pid' => $pageId,
            'language' => $options['language'],
            'includeHiddenPages' => $options['includeHiddenPages'],
            'includeHiddenContent' => $options['includeHiddenContent'],
            'includeDeletedRecords' => $options['includeDeletedRecords'],
        ], function () use ($pageId, $options) {
            return $this->typoScriptService->renderContentObject('CONTENT', [
                'table' => 'tt_content',
                'select.' => [
                    'pidInList' => $pageId,
                    'languageField' => $GLOBALS['TCA']['tt_content']['ctrl']['languageField'] ?? 'sys_language_uid',
                    'orderBy' => 'sorting',
                    'where' => '{#colPos}=' . $options['colPos'],
                ],
            ]);
        });
    }
    
    /**
     * Prepares and validates the given options
     *
     * @param   array  $options
     *
     * @return array
     */
    protected function prepareOptions(array $options): array
    {
        return Options::make($options, [
            'language' => [
                'type' => ['int', 'string', 'null', SiteLanguage::class],
                'default' => null,
            ],
            'includeHiddenPages' => [
                'type' => 'bool',
                'default' => false,
            ],
            'includeHiddenContent' => [
                'type' => 'bool',
                'default' => false,
            ],
            'includeDeletedRecords' => [
                'type' => 'bool',
                'default' => false,
            ],
            'force' => [
                'type' => 'bool',
                'default' => false,
            ],
            'colPos' => [
                'type' => 'int',
                'default' => 0,
            ],
            'site' => [
                'type' => ['string', 'null'],
                'default' => null,
            ],
        ]);
    }
}