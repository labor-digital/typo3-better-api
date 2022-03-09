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
 * Last modified: 2022.03.09 at 13:25
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Page\Util\Content;


use LaborDigital\T3ba\Tool\Simulation\EnvironmentSimulator;
use LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ContentRepository;
use LaborDigital\T3ba\Tool\Tsfe\TsfeService;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class ContentResolver implements SingletonInterface
{
    /**
     * @var \LaborDigital\T3ba\Tool\Simulation\EnvironmentSimulator
     */
    protected EnvironmentSimulator $simulator;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ContentRepository
     */
    protected ContentRepository $contentRepository;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Tsfe\TsfeService
     */
    protected TsfeService $tsfeService;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Page\Util\Content\ContentSorter
     */
    protected ContentSorter $contentSorter;
    
    public function __construct(
        EnvironmentSimulator $simulator,
        ContentRepository $contentRepository,
        TsfeService $tsfeService,
        ContentSorter $contentSorter
    )
    {
        $this->simulator = $simulator;
        $this->contentRepository = $contentRepository;
        $this->tsfeService = $tsfeService;
        $this->contentSorter = $contentSorter;
    }
    
    /**
     * Internal implementation detail of PageService::getPageContents()
     *
     * @param   int    $pageId
     * @param   array  $options
     *
     * @return array
     * @see \LaborDigital\T3ba\Tool\Page\PageService::getPageContents()
     */
    public function getContent(int $pageId, array $options = []): array
    {
        $options = $this->prepareOptions($options);
        
        return $this->simulator->runWithEnvironment([
            'site' => $options['site'],
            'asAdmin' => $options['force'],
            'language' => $options['language'],
            'includeHiddenPages' => $options['includeHiddenPages'],
            'includeHiddenContent' => $options['includeHiddenContent'],
            'includeDeletedRecords' => $options['includeDeletedRecords'],
        ], function () use ($pageId, $options) {
            $records = $this->findRecords($pageId, $options['where']);
            
            if ($options['includeExtensionFields']) {
                $records = $this->extendRecords($records, $options['remapExtensionFields']);
            }
            
            if ($options['returnRaw']) {
                return $records;
            }
            
            return $this->contentSorter->sort($pageId, $records);
        });
    }
    
    /**
     * Resolves the tt_content records based on the given pid and optional, additional where clause
     *
     * @param   int     $pageId
     * @param   string  $additionalWhere
     *
     * @return array
     */
    protected function findRecords(int $pageId, string $additionalWhere): array
    {
        // @todo in v11 when I rewrote the BetterQuery classes this should be able to do without the ContentObjectRenderer
        $records = $this->tsfeService->getContentObjectRenderer()->getRecords('tt_content', [
            'pidInList' => $pageId,
            'where' => $additionalWhere,
        ]);
        
        if (! is_array($records)) {
            return [];
        }
        
        return $records;
    }
    
    /**
     * Will extend the tt_content rows with their ContentType extension fields where possible.
     *
     * @param   array  $records
     * @param   bool   $remap
     *
     * @return array
     */
    protected function extendRecords(array $records, bool $remap): array
    {
        foreach ($records as $k => $record) {
            $records[$k] = $this->contentRepository->getExtendedRow($record, $remap);
        }
        
        return $records;
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
            'where' => [
                'type' => 'string',
                'default' => '',
            ],
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
            'returnRaw' => [
                'type' => 'bool',
                'default' => false,
            ],
            'force' => [
                'type' => 'bool',
                'default' => false,
            ],
            'site' => [
                'type' => ['string', 'null'],
                'default' => null,
            ],
            // @todo make this the default in v11
            'includeExtensionFields' => [
                'type' => 'bool',
                'default' => false,
            ],
            'remapExtensionFields' => [
                'type' => 'bool',
                'default' => true,
            ],
        ]);
    }
}