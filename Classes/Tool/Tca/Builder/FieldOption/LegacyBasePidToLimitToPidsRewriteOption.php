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
 * Last modified: 2021.12.16 at 13:46
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

/**
 * @deprecated temporary implementation that can be removed in v12 when
 *             the "basePid" option was removed from applyRelationSelect()
 */
class LegacyBasePidToLimitToPidsRewriteOption extends AbstractOption
{
    /**
     * Stores the value to be transferred to the limitToPids option
     *
     * @var mixed
     */
    protected $value;
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['basePid'] = [
            'type' => ['int', 'null', 'string', 'true'],
            'default' => null,
            'filter' => function ($v, $_, $options) {
                if ($v === null) {
                    return;
                }
                
                $table = $this->context->getTcaTable()->getTableName();
                $field = $this->context->getField()->getId();
                
                trigger_error(
                    'Deprecated option in: ' . $table . '::' . $field . '. The "basePid" option will be removed in v12, use the "limitToPids"',
                    E_USER_DEPRECATED
                );
                
                $this->value = $v;
                
                return null;
            },
        ];
        $definition['limitToPids']['preFilter'] = function ($v) {
            if (isset($this->value)) {
                return $this->value;
            }
            
            return $v;
        };
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void { }
    
}