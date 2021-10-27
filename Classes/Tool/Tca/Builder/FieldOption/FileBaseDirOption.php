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
 * Last modified: 2021.10.26 at 11:12
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;

/**
 * Applies the "baseDir" option for file fields
 */
class FileBaseDirOption extends AbstractOption
{
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['baseDir'] = [
            'type' => 'string',
            'default' => $this->context->getConfigFacet()->getConfigValue('tca.fieldPresetOptions.fileDefaultBaseDir', ''),
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        if (empty($options['baseDir'])) {
            return;
        }
        
        $config['baseDir'] = $options['baseDir'];
        
        if (! $this->context->isInFlexFormSection() &&
            $this->context->getConfigFacet()->getConfigValue('tca.fieldPresetOptions.fileBaseDirEnablesUpload', true)) {
            $config['appearance']['fileUploadAllowed'] = true;
            
            // Interop with the online media upload field, when a base dir exists
            $allowList = Arrays::makeFromStringList((string)$options['allowList']);
            $onlineMediaAllowed = OnlineMediaHelperRegistry::getInstance()->getSupportedFileExtensions();
            if (! empty($allowList)) {
                $onlineMediaAllowed = array_intersect($allowList, $onlineMediaAllowed);
            }
            
            if (! empty($onlineMediaAllowed)) {
                $config['appearance']['fileByUrlAllowed'] = true;
            }
        }
    }
    
}