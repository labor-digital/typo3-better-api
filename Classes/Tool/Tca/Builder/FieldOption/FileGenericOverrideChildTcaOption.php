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
 * Last modified: 2021.10.26 at 12:11
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

use LaborDigital\T3ba\FormEngine\UserFunc\FileGenericOverrideChildTcaDataProvider;

/**
 * Special option for the FAL file field preset to override the table show item, based on the type,
 * while inheriting the showItem string from the actual sys_file_reference TCA table
 */
class FileGenericOverrideChildTcaOption extends AbstractOption
{
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['disableFalFields'] = [
            'type' => ['array', 'true'],
            'default' => $this->context
                ->getConfigFacet()
                ->getConfigValue('tca.fieldPresetOptions.fileDefaultDisabledFalFields', []),
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        if ($this->context->isInFlexFormSection()) {
            return;
        }
        
        $config[FileGenericOverrideChildTcaDataProvider::CONFIG_KEY] = true;
        $config[FileGenericOverrideChildTcaDataProvider::DISABLE_FAL_FIELDS_KEY] = $options['disableFalFields'];
    }
    
    
}