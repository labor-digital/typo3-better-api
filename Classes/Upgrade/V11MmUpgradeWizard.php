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
 * Last modified: 2021.07.19 at 15:43
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Upgrade;


use LaborDigital\T3ba\T3baFeatureToggles;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;

class V11MmUpgradeWizard extends AbstractChunkedUpgradeWizard
{
    protected $selectFields = ['uid_local', 'uid_foreign', 'sorting', 'sorting_foreign'];
    
    /**
     * @inheritDoc
     */
    public function executeUpdate(): bool
    {
        foreach ($GLOBALS['TCA'] as $tableName => $tableTca) {
            foreach ($tableTca['columns'] ?? [] as $colName => $colTca) {
                if (isset($colTca['config']['t3ba']['deprecated'][static::class])) {
                    $this->processSingleColumn($tableName, $colName, $colTca['config']);
                }
            }
        }
        
        return true;
    }
    
    protected function processSingleColumn(string $tableName, string $colName, array $config): void
    {
        $legacyTableName = $this->buildLegacyMmTableName($tableName, $colName);
        if (! $this->hasLegacyTable($legacyTableName)) {
            $this->output->writeln('Skipping mm table: ' . $legacyTableName . ' because the table was not found');
            
            return;
        }
        
        $newMmTable = $config['MM'];
        $defaults = $config['MM_match_fields'] ?? [];
        
        $updateQuery = $this->getQuery($newMmTable);
        $count = 0;
        
        $this->setMainTable($legacyTableName);
        while ($chunk = $this->getChunk()) {
            foreach ($chunk as $row) {
                $newRow = array_merge($defaults, $row);
                
                if ($updateQuery->withWhere($newRow)->getCount() > 0) {
                    // Ignore exact matches on reruns
                    continue;
                }
                
                $count++;
                $updateQuery->insert($newRow);
            }
        }
        
        if (! empty($count)) {
            $this->output->writeln('Migrated ' . $count . ' rows from ' . $legacyTableName . ' to ' . $newMmTable);
        }
    }
    
    protected function buildLegacyMmTableName(string $tableName, string $colName): string
    {
        $mmTableName = str_replace('_domain_model_', '_', $tableName) . '_' . Inflector::toUnderscore($colName);
        if (strlen($mmTableName) > 125) {
            $mmNameHash = md5($mmTableName);
            $mmTableName = substr($mmTableName, 0, 125 - 32 - 1); // max length - md5 length - 1 for "_"
            $mmTableName .= '_' . $mmNameHash;
        }
        
        return $mmTableName . '_mm';
    }
    
    protected function hasLegacyTable(string $legacyTableName): bool
    {
        try {
            $query = $this->getQuery($legacyTableName);
            
            return $query->getCount() > -1;
        } catch (\Throwable $e) {
            return false;
        }
    }
    
    /**
     * @inheritDoc
     */
    public function updateNecessary(): bool
    {
        if (! $this->cs()->typoContext->config()->isFeatureEnabled(T3baFeatureToggles::TCA_V11_MM_TABLES)) {
            return false;
        }
        
        foreach ($GLOBALS['TCA'] as $tableTca) {
            foreach ($tableTca['columns'] ?? [] as $colTca) {
                if (isset($colTca['config']['t3ba']['deprecated'][static::class])) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Upgrades the mm relations generated using the TCA builder, to use only a single MM table per table';
    }
    
    /**
     * @inheritDoc
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }
    
}