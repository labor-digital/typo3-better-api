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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\DataHook\FieldPacker;


use LaborDigital\T3ba\Tool\DataHook\Definition\DataHookDefinition;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FlexFormFieldPacker implements FieldPackerInterface
{
    /**
     * @inheritDoc
     */
    public function unpackFields(DataHookDefinition $definition): array
    {
        $flexFields = [];
        foreach ($definition->data as $fieldName => $value) {
            if (is_array($value)
                || ($definition->tca['columns'][$fieldName]['config']['type'] ?? null) !== 'flex') {
                continue;
            }
            
            $value = empty($value) ? [] : GeneralUtility::xml2array($value);
            $definition->data[$fieldName] = is_array($value) ? $value : [];
            $flexFields[] = $fieldName;
        }
        
        return $flexFields;
    }
    
    /**
     * @inheritDoc
     */
    public function packFields(DataHookDefinition $definition, array $fieldsToPack): void
    {
        $tools = TypoContext::getInstance()->di()->makeInstance(FlexFormTools::class);
        foreach ($fieldsToPack as $fieldName) {
            $value = $definition->data[$fieldName];
            
            if (! is_array($value)) {
                continue;
            }
            
            $definition->data[$fieldName] = $tools->flexArray2Xml($value, true);
        }
    }
    
}
