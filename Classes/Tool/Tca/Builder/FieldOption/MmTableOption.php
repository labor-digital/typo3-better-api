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
 * Last modified: 2021.10.25 at 14:22
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\TextType;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\TcaPostProcessor;
use LaborDigital\T3ba\T3baFeatureToggles;
use LaborDigital\T3ba\Tool\Sql\SqlFieldLength;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException;
use LaborDigital\T3ba\Upgrade\V11MmUpgradeWizard;
use Neunerlei\Inflection\Inflector;

/**
 * Option to configure the usage of a mm table on in a field preset
 */
class MmTableOption extends AbstractOption
{
    /**
     * The list of opposite table names or null if there is no opposite option enabled
     *
     * @var null|array|string
     */
    protected $oppositeTables;
    
    /**
     * This is mostly an implementation detail of the "categorize" field preset but considered part of the public api.
     * If this property contains a string, ONLY the mmOpposite configuration will be applied with the given field name as opposite field
     *
     * @var string|null
     */
    protected $forcedOppositeField;
    
    public function __construct($oppositeTables = null, ?string $forcedOppositeField = null)
    {
        $this->oppositeTables = $oppositeTables;
        $this->forcedOppositeField = $forcedOppositeField;
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        if ($this->oppositeTables) {
            $definition['mmOpposite'] = [
                'type' => ['string', 'null'],
                'default' => null,
            ];
        }
        
        $definition['mmTable'] = [
            'type' => 'bool',
            'default' => static function ($field, $given) {
                // Interop with the "maxItems" method -> If only a single item is allowed -> no MM table is required
                if (! isset($given['maxItems'])) {
                    return true;
                }
                
                return ! ((int)$given['maxItems'] === 1);
            },
        ];
        
        $definition['mmTableName'] = [
            'type' => 'string',
            'default' => '',
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        // Skip if we should not use a mm table
        if ($options['mmTable'] === false) {
            $this->context->configureSqlColumn(
                static function (Column $column) {
                    $column->setType(new TextType())
                           ->setNotnull(false)
                           ->setLength(SqlFieldLength::TEXT)
                           ->setDefault('');
                }
            );
            
            return;
        }
        
        // MM Tables are not supported in sections
        if ($this->context->isInFlexFormSection()) {
            unset($config['MM']);
            
            return;
        }
        
        // Opposite configuration only if required
        if (! empty($this->oppositeTables) && (! empty($options['mmOpposite']) || $this->forcedOppositeField !== null)) {
            $this->applyOppositeConfig($config, $options);
        }
        
        $this->v11DefinitionSwitch($config, $options, function () use ($options, &$config) {
            $tableName = $this->context->getTcaTable()->getTableName();
            $sqlRegistry = $this->context->cs()->sqlRegistry;
            
            $config['MM'] = $sqlRegistry->registerMmTable(
                ! empty($options['mmTableName']) ? $options['mmTableName'] : $sqlRegistry->makeMmTableName($tableName)
            );
            
            $config['MM_match_fields'] = [
                'fieldname' =>
                    $this->context->isFlexForm()
                        ? $this->context->getTcaField()->getId() . '->' . $this->context->getField()->getId()
                        : $this->context->getField()->getId(),
            ];
        });
        
        
        $this->context->configureSqlColumn(
            static function (Column $column) {
                $column->setType(new IntegerType())
                       ->setNotnull(true)
                       ->setLength(11)
                       ->setDefault(0);
            }
        );
        
        $config['prepend_tname'] = true;
        
        $config['t3ba']['deprecated'][V11MmUpgradeWizard::class] = true;
    }
    
    /**
     * Registers the mmOpposite configuration in the current field config, and registers a post processor
     * on the target table to generate the required configuration there as well
     *
     * @param   array  $config
     * @param   array  $options
     */
    public function applyOppositeConfig(array &$config, array $options): void
    {
        $oppositeTables = $this->context->getRealTableNameList($this->oppositeTables);
        if (count($oppositeTables) > 1) {
            throw new TcaBuilderException(
                'mmOpposite does not work if your relation does not resolve to EXACTLY ONE foreign table.' .
                ' Your field: ' . $this->context->getField()->getId() . ' applies to multiple: ' . implode(', ', $oppositeTables));
        }
        
        $localField = $this->context->getField()->getId();
        $targetField = $this->forcedOppositeField ?? $options['mmOpposite'];
        $localTable = $this->context->getTcaTable()->getTableName();
        $targetTable = (string)$oppositeTables[0];
        
        $config['MM_opposite_field'] = $targetField;
        $config['MM_match_fields']['tablenames'] = $localTable;
        
        TcaPostProcessor::registerAdditionalProcessor(
            $targetTable,
            static function (array &$config) use ($localTable, $localField, $targetField) {
                // Ignore if the local field was removed later
                if (! isset($GLOBALS['TCA'][$localTable]['columns'][$localField])) {
                    return;
                }
                
                $usage = $config['columns'][$targetField]['config']['MM_oppositeUsage'][$localTable] ?? [];
                
                $config['columns'][$targetField]['config']['MM_oppositeUsage'][$localTable]
                    = array_unique(array_merge($usage, [$localField]));
            }
        );
    }
    
    /**
     * @param   array     $config
     * @param   array     $options
     * @param   callable  $newCode
     *
     * @deprecated will be removed in v12 in favor of $newCode
     */
    protected function v11DefinitionSwitch(array &$config, array $options, callable $newCode): void
    {
        if ($this->context->cs()->typoContext->config()->isFeatureEnabled(T3baFeatureToggles::TCA_V11_MM_TABLES)) {
            $newCode();
        } else {
            $tableName = $this->context->getTcaTable()->getTableName();
            $sqlRegistry = $this->context->cs()->sqlRegistry;
            
            $fieldId = Inflector::toUnderscore($this->context->getField()->getId());
            if ($this->context->isFlexForm()) {
                $fieldId = 'flex_' . Inflector::toUnderscore($this->context->getTcaField()->getId()) . '_' . $fieldId;
            }
            
            $config['MM'] = $sqlRegistry->registerMmTable(
                ! empty($options['mmTableName'])
                    ? $options['mmTableName']
                    : $sqlRegistry->makeMmTableName($tableName, $fieldId)
            );
        }
    }
}