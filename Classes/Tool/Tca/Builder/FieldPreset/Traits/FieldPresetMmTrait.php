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
 * Last modified: 2021.07.20 at 14:49
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\TextType;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\TcaPostProcessor;
use LaborDigital\T3ba\T3baFeatureToggles;
use LaborDigital\T3ba\Tool\Sql\SqlFieldLength;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException;
use LaborDigital\T3ba\Upgrade\V11MmUpgradeWizard;
use Neunerlei\Inflection\Inflector;

trait FieldPresetMmTrait
{
    protected function addMmTableOptions(array $optionDefinition, bool $withOpposite = true): array
    {
        if ($withOpposite) {
            $optionDefinition['mmOpposite'] = [
                'type' => ['string', 'null'],
                'default' => null,
            ];
        }
        
        $optionDefinition['mmTable'] = [
            'type' => 'bool',
            'default' => static function ($field, $given) {
                if (! isset($given['maxItems'])) {
                    return true;
                }
                
                return ! ((int)$given['maxItems'] === 1);
            },
        ];
        $optionDefinition['mmTableName'] = [
            'type' => 'string',
            'default' => '',
        ];
        
        return $optionDefinition;
    }
    
    /**
     * Internal helper to configure an mm table for the current field.
     *
     * @param   array  $config  The current "config" array of the field, to add the mm table to
     * @param   array  $options
     *
     * @return array
     */
    protected function addMmTableConfig(array $config, array $options): array
    {
        // Skip if we should not use an mm table
        if ($options['mmTable'] === false) {
            $this->configureSqlColumn(static function (Column $column) {
                $column->setType(new TextType())
                       ->setNotnull(false)
                       ->setLength(SqlFieldLength::TEXT)
                       ->setDefault('');
            });
            
            return $config;
        }
        
        // MM Tables are not supported in sections
        if ($this->isInFlexFormSection()) {
            unset($config['MM']);
            
            return $config;
        }
        
        $tableName = $this->getTcaTable()->getTableName();
        /** @var \LaborDigital\T3ba\Tool\Sql\SqlRegistry $sqlRegistry */
        $sqlRegistry = $this->context->cs()->sqlRegistry;
        
        if ($this->cs()->typoContext->config()->isFeatureEnabled(T3baFeatureToggles::TCA_V11_MM_TABLES)) {
            $config['MM'] = $sqlRegistry->registerMmTable(
                ! empty($options['mmTableName']) ? $options['mmTableName'] : $sqlRegistry->makeMmTableName($tableName)
            );
            
            $config['MM_match_fields'] = [
                'fieldname' =>
                    $this->isFlexForm()
                        ? $this->getTcaField()->getId() . '->' . $this->field->getId()
                        : $this->field->getId(),
            ];
            
        } else {
            // @todo remove this in v12
            $fieldId = Inflector::toUnderscore($this->field->getId());
            if ($this->isFlexForm()) {
                $fieldId = 'flex_' . Inflector::toUnderscore($this->getTcaField()->getId()) . '_' . $fieldId;
            }
            
            $config['MM'] = $sqlRegistry->registerMmTable(
                ! empty($options['mmTableName']) ? $options['mmTableName'] : $sqlRegistry->makeMmTableName($tableName, $fieldId)
            );
        }
        
        $config['t3ba']['deprecated'][V11MmUpgradeWizard::class] = true;
        
        $this->configureSqlColumn(static function (Column $column) {
            $column->setType(new IntegerType())
                   ->setNotnull(true)
                   ->setLength(11)
                   ->setDefault(0);
        });
        
        $config['prepend_tname'] = true;
        
        return $config;
    }
    
    /**
     * Registers the mmOpposite configuration in the current field config, and registers a post processor
     * on the target table to generate the required configuration there as well
     *
     * @param   array  $config
     * @param   array  $options
     * @param   array  $tableNames
     *
     * @return array
     * @throws \LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException
     */
    protected function addMmOppositeConfig(array $config, array $options, array $tableNames): array
    {
        if ((isset($options['mmTable']) && $options['mmTable'] === false) || empty($options['mmOpposite'])) {
            return $config;
        }
        
        if (count($tableNames) > 1) {
            throw new TcaBuilderException(
                'mmOpposite does not work if your relation does not resolve to EXACTLY ONE foreign table.' .
                ' Your field: ' . $this->field->getId() . ' applies to multiple: ' . implode(', ', $tableNames));
        }
        
        $localField = $this->field->getId();
        $targetField = $options['mmOpposite'];
        $localTable = $this->getTcaTable()->getTableName();
        $targetTable = reset($tableNames);
    
        $config['MM_opposite_field'] = $targetField;
        $config['MM_match_fields']['tablenames'] = $localTable;
        
        TcaPostProcessor::registerAdditionalProcessor($targetTable, static function (array &$config) use ($localTable, $localField, $targetField) {
            // Ignore if the local field was removed later
            if (! isset($GLOBALS['TCA'][$localTable]['columns'][$localField])) {
                return;
            }
            
            $usage = $config['columns'][$targetField]['config']['MM_oppositeUsage'][$localTable] ?? [];
            
            $config['columns'][$targetField]['config']['MM_oppositeUsage'][$localTable] = array_unique(array_merge($usage, [$localField]));
        });
        
        return $config;
    }
}