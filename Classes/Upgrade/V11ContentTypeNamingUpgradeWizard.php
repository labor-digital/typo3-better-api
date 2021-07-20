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


use LaborDigital\T3ba\ExtConfigHandler\UpgradeWizard\AbstractChunkedUpgradeWizard;
use LaborDigital\T3ba\T3baFeatureToggles;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;

class V11ContentTypeNamingUpgradeWizard extends AbstractChunkedUpgradeWizard
{
    protected $selectFields = null;
    protected $chunkSize = 20;
    protected $includeDeleted = true;
    
    /**
     * @inheritDoc
     */
    public function executeUpdate(): bool
    {
        foreach ($GLOBALS['TCA'] as $tableName => $tableTca) {
            if (! str_starts_with($tableName, 'tt_content_')) {
                continue;
            }
            
            $legacyTableName = 'ct_' . substr($tableName, 11);
            if (! $this->hasLegacyTable($legacyTableName)) {
                continue;
            }
            
            $this->migrateSingleTable($tableName, $legacyTableName);
        }
        
        $this->migrateContentTable();
        
        return true;
    }
    
    protected function migrateSingleTable(string $tableName, string $legacyTableName): void
    {
        $this->setMainTable($legacyTableName);
        
        $insertQuery = $this->getQuery($tableName);
        
        $count = 0;
        while ($chunk = $this->getChunk()) {
            foreach ($chunk as $row) {
                if ($insertQuery->withWhere(['uid' => $row['uid']])->getCount() > 0) {
                    continue;
                }
                
                $insertQuery->insert($row);
                $count++;
            }
        }
        
        if (! empty($count)) {
            $this->output->writeln('Migrated ' . $count . ' rows from table ' . $legacyTableName . ' to table ' . $tableName);
        }
    }
    
    protected function migrateContentTable(): void
    {
        $this->setMainTable('tt_content');
        $this->selectFields = ['ct_child', 'uid'];
        $this->where = ['ct_child !=' => '0'];
        $this->chunkSize = 200;
        
        $updateQuery = $this->getQuery()->withIncludeDeleted();
        
        $count = 0;
        while ($chunk = $this->getChunk()) {
            foreach ($chunk as $row) {
                $updateQuery->withWhere(['uid' => $row['uid']])
                            ->update(['t3ba_ct_child' => $row['ct_child']]);
                $count++;
            }
        }
        
        if (! empty($count)) {
            $this->output->writeln('Migrated ' . $count . ' rows in the tt_content table');
        }
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
        if (! $this->cs()->typoContext->config()->isFeatureEnabled(T3baFeatureToggles::CONTENT_TYPE_V11_NAMING_SCHEMA)) {
            return false;
        }
        
        foreach ($GLOBALS['TCA'] as $tableName => $tableTca) {
            if (str_starts_with($tableName, 'tt_content_')) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Migrates the v10 content type extension table names to the v11 naming schema';
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