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
 * Last modified: 2021.10.26 at 09:22
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

/**
 * @deprecated temporary implementation, will be removed in v12
 */
class LegacyReadOnlyOption extends AbstractOption
{
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['readOnly'] = [
            'type' => 'bool',
            'default' => false,
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        if ($options['readOnly'] === true) {
            $table = $this->context->getTcaTable()->getTableName();
            $field = $this->context->getField()->getId();
            
            trigger_error(
                'Deprecated option in: ' . $table . '::' . $field . '. The "readOnly" option will be removed in v12, use the setReadOnly() method on a field instead',
                E_USER_DEPRECATED
            );
            
            $config['readOnly'] = true;
        }
    }
    
}