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
 * Last modified: 2021.07.19 at 14:27
 */

declare(strict_types=1);

namespace LaborDigital\T3ba\Upgrade;

use LaborDigital\T3ba\ExtConfigHandler\UpgradeWizard\AbstractChunkedUpgradeWizard;
use LaborDigital\T3ba\T3baFeatureToggles;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;

class V11InlineUpgradeWizard extends AbstractChunkedUpgradeWizard
{
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
    
    protected function processSingleColumn(string $tableName, string $colName, array $config)
    {
        $this->setMainTable($tableName);
        
        $updateQuery = $this->getQuery($config['foreign_table'])
                            ->withIncludeDeleted()
                            ->withWhere([$config['foreign_table_field'] => ''], 'tableEmpty');
        
        $foreignFields = $config['foreign_match_fields'] ?? [];
        $notWhere = array_combine(
            array_map(static function ($v) { return $v . ' !='; }, array_keys($foreignFields)),
            $foreignFields
        );
        
        if (! empty($notWhere)) {
            $updateQuery = $updateQuery->withWhere($notWhere, 'notDefaultValues');
        }
        
        $count = 0;
        while ($chunk = $this->getChunk()) {
            foreach ($chunk as $row) {
                $q = $updateQuery->withWhere([$config['foreign_field'] => $row['uid']]);
                if ($q->getCount() === 0) {
                    continue;
                }
                
                $updateRow = array_merge(
                    $config['foreign_match_fields'] ?? [],
                    [
                        $config['foreign_table_field'] => $tableName,
                    ]
                );
                
                $q->update($updateRow);
                $count++;
            }
        }
        
        if (! empty($count)) {
            $this->output->writeln('Migrated ' . $count . ' relations from table ' . $tableName . ' to table ' . $config['foreign_table']);
        }
    }
    
    /**
     * @inheritDoc
     */
    public function updateNecessary(): bool
    {
        if (! $this->cs()->typoContext->config()->isFeatureEnabled(T3baFeatureToggles::TCA_V11_INLINE_RELATIONS)) {
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
        return 'Upgrades the inline definitions generated using the TCA builder, to use an MM table instead of foreign table fields';
    }
    
    /**
     * @inheritDoc
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
            V11MmUpgradeWizard::class,
        ];
    }
}