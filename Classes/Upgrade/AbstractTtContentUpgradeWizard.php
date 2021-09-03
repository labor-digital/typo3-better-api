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
 * Last modified: 2021.09.01 at 19:52
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Upgrade;

use LaborDigital\T3ba\Tool\Database\BetterQuery\Standalone\StandaloneBetterQuery;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use RuntimeException;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Special variant of the chunked upgrade wizard to work with the "tt_content" table.
 * it automatically traverses all currently active languages for an element and upgrades them independently.
 */
abstract class AbstractTtContentUpgradeWizard extends AbstractChunkedUpgradeWizard
{
    /**
     * Switch to disable grid elements resolving even if the extension is loaded
     *
     * @var bool
     */
    protected $useGridElementsIfLoaded = true;
    
    /**
     * @var SiteLanguage
     */
    protected $language;
    
    /**
     * Executes the update for a single language, all internal methods will be prepared for the
     * language when this is being executed.
     *
     * @param   \TYPO3\CMS\Core\Site\Entity\SiteLanguage  $language
     */
    abstract protected function processLanguage(SiteLanguage $language): bool;
    
    /**
     * @inheritDoc
     */
    public function executeUpdate(): bool
    {
        $status = true;
        
        foreach ($this->cs()->typoContext->site()->getAll() as $site) {
            foreach ($site->getAllLanguages() as $language) {
                $this->output->writeln('-------------------------------------------------------------');
                $this->output->writeln('STARTING LANGUAGE ' . $language->getNavigationTitle());
                $this->output->writeln('-------------------------------------------------------------');
                $this->language = $language;
                $this->chunks = null;
                $this->count = 0;
                $status = $this->cs()->simulator->runWithEnvironment(
                    ['language' => $language, 'pid' => $site->getRootPageId()],
                    function () use ($language) {
                        return $this->processLanguage($language);
                    }
                );
                
                if (! $status) {
                    $this->output->writeln(
                        'ERROR! Processing of language: ' . $language->getLocale() .
                        ' failed for site: ' . $site->getIdentifier());
                    break 2;
                }
            }
        }
        
        return $status;
    }
    
    /**
     * Moves a content record on the same page to a new location relative to the given pivot row
     *
     * @param   int    $movingUid  The uid of the tt_content row to move
     * @param   array  $pivotRow   The FULL db row of the tt_content row that is used as pivot
     * @param   bool   $before     True to move the element with $movingUid in front of $pivotRow, false to move it after $pivotRow
     */
    protected function moveRecordToRow(int $movingUid, array $pivotRow, bool $before): void
    {
        if ($before) {
            $pivotUid = $this->getPreviousUid($pivotRow);
        } else {
            if (! isset($pivotRow['uid'])) {
                throw new RuntimeException('Invalid pivot row! ' . SerializerUtil::serializeJson($pivotRow));
            }
            
            $pivotUid = -$pivotRow['uid'];
        }
        
        $this->getDataHandler()->move($movingUid, $pivotUid, true);
    }
    
    /**
     * Internal helper to resolve the previous uid in the layout relative to the given row
     *
     * @param   array  $row  The tt_content row to find the previous row for
     *
     * @return int The result is either the NEGATIVE uid of the previous element,
     * or the POSITIVE uid of the page that contains the row
     */
    protected function getPreviousUid(array $row): int
    {
        $useGridElements = $this->useGridElementsIfLoaded && ExtensionManagementUtility::isLoaded('gridelements');
        
        $neededCols = ['sorting', 'pid', 'colPos'];
        if ($useGridElements) {
            $neededCols[] = 'tx_gridelements_container';
        }
        
        if (count(array_intersect(array_keys($row), $neededCols)) !== count($neededCols)) {
            throw new RuntimeException(
                'Invalid row, missing either: ' . implode(', ', $neededCols) . ' columns',
                SerializerUtil::serializeJson($row));
        }
        
        $result = $this->getQuery()
                       ->withWhere(
                           array_merge(
                               [
                                   'colPos' => $row['colPos'],
                                   'pid' => $row['pid'],
                                   'sorting <' => $row['sorting'],
                               ],
                               $useGridElements
                                   ? ['tx_gridelements_container' => $row['tx_gridelements_container']]
                                   : []
                           )
                       )
                       ->withOrder('sorting', 'asc')
                       ->getAll([
                           'uid',
                           'sorting',
                           'pid',
                           'colPos',
                       ]);
        
        if (empty($result)) {
            return (int)$row['pid'];
        }
        
        return -((int)end($result)['uid']);
    }
    
    /**
     * @inheritDoc
     */
    protected function getQuery(?string $tableName = null): StandaloneBetterQuery
    {
        $query = parent::getQuery($tableName);
        
        if ($this->language) {
            $query = $query->withLanguage($this->language);
        }
        
        return $query;
    }
    
}