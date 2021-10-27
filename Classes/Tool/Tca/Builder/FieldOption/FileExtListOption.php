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
 * Last modified: 2021.10.26 at 11:05
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\FieldPresetContext;

/**
 * Adds an "allowList" and "blockList" option for a FAL file field
 */
class FileExtListOption extends AbstractOption
{
    /**
     * @var string|null
     */
    protected $defaultAllowList;
    
    /**
     * @var string|null
     * @deprecated Will be removed in v12
     */
    protected $defaultBlockList;
    
    public function __construct(?string $defaultAllowList = null, ?string $defaultBlockList = null)
    {
        $this->defaultAllowList = $defaultAllowList;
        $this->defaultBlockList = $defaultBlockList ?? '';
    }
    
    /**
     * @inheritDoc
     */
    public function initialize(FieldPresetContext $context): void
    {
        if ($this->defaultAllowList === null) {
            $this->defaultAllowList
                = implode(',', array_filter(
                    $context->getConfigFacet()
                            ->getConfigValue('tca.fieldPresetOptions.fileDefaultAllowList', [])
                )
            );
        }
        
        parent::initialize($context);
    }
    
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['allowList'] = [
            'type' => 'string',
            'default' => $this->defaultAllowList,
            'preFilter' => static function ($v) {
                return is_array($v) ? implode(',', $v) : $v;
            },
        ];
        
        $definition['blockList'] = [
            'type' => 'string',
            'default' => $this->defaultBlockList,
            'preFilter' => static function ($v) {
                return is_array($v) ? implode(',', $v) : $v;
            },
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        if (! empty($options['blockList'])) {
            $table = $this->context->getTcaTable()->getTableName();
            $field = $this->context->getField()->getId();
            
            trigger_error(
                'Deprecated option in: ' . $table . '::' . $field . '. The "blockList" option will be removed in v12 with no replacement',
                E_USER_DEPRECATED
            );
            
        }
        
        // The implications are to complex to handle here, so this option only provides a definition for the presets to use
    }
    
}