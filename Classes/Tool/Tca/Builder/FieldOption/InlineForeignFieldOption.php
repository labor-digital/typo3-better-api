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
 * Last modified: 2021.10.26 at 09:08
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;


use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\TcaPostProcessor;
use LaborDigital\T3ba\T3baFeatureToggles;
use LaborDigital\T3ba\Upgrade\V11InlineUpgradeWizard;

class InlineForeignFieldOption extends AbstractOption
{
    protected $foreignTable;
    
    public function __construct($foreignTable)
    {
        $this->foreignTable = $foreignTable;
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['foreignField'] = [
            'type' => 'string',
            'default' => 't3ba_inline',
            'validator' => [$this, 'fieldLengthValidator'],
        ];
        $definition['foreignSortByField'] = [
            'type' => 'string',
            'default' => 't3ba_inline_sorting',
            'validator' => [$this, 'fieldLengthValidator'],
        ];
        $definition['foreignTableNameField'] = [
            'type' => 'string',
            'default' => 't3ba_inline_table',
            'validator' => [$this, 'fieldLengthValidator'],
        ];
        $definition['foreignFieldNameField'] = [
            'type' => 'string',
            'default' => 't3ba_inline_field',
            'validator' => [$this, 'fieldLengthValidator'],
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        $foreignTableName = $this->context->getRealTableName($this->foreignTable);
        
        $config['foreign_table'] = $foreignTableName;
        $config['foreign_field'] = $options['foreignField'];
        $config['foreign_sortby'] = $options['foreignSortByField'];
        
        // Add columns to foreign table
        $sqlTable = $this->context->cs()->sqlRegistry->getTableOverride($foreignTableName);
        foreach ([$options['foreignField'], $options['foreignSortByField']] as $foreignField) {
            if (! $sqlTable->hasColumn($foreignField, true)) {
                $sqlTable->addColumn($foreignField, 'integer')
                         ->setDefault(0)
                         ->setLength(11);
            }
            
            TcaPostProcessor::registerAdditionalProcessor(
                $foreignTableName,
                static function (array &$config) use ($foreignField) {
                    if (isset($config['columns'][$foreignField])) {
                        return;
                    }
                    
                    $config['columns'][$foreignField]['config']['type'] = 'passtrough';
                }
            );
        }
        
        $this->v11DefinitionSwitch(function () use (&$config, $options, $sqlTable) {
            $config['foreign_match_fields'] = [$options['foreignFieldNameField'] => $this->context->getField()->getId()];
            $config['foreign_table_field'] = $options['foreignTableNameField'];
            
            foreach ([$options['foreignFieldNameField'], $options['foreignTableNameField']] as $foreignField) {
                if (! $sqlTable->hasColumn($foreignField, true)) {
                    $sqlTable->addColumn($foreignField, 'string')
                             ->setDefault('')
                             ->setLength(128);
                }
            }
        });
        
        $config['t3ba']['deprecated'][V11InlineUpgradeWizard::class] = true;
    }
    
    /**
     * @param   callable  $newCode
     *
     * @deprecated will be removed in v12 in favor of $newCode
     */
    protected function v11DefinitionSwitch(callable $newCode): void
    {
        if ($this->context->cs()->typoContext->config()->isFeatureEnabled(T3baFeatureToggles::TCA_V11_INLINE_RELATIONS)) {
            $newCode();
        }
    }
    
    public function fieldLengthValidator($v)
    {
        if (strlen($v) > 64) {
            return 'The configured field is too long, you a field name can have 64 characters at max!';
        }
        
        return true;
    }
    
}