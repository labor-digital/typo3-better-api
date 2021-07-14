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
 * Last modified: 2021.07.14 at 16:57
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\TcaPostProcessorStepInterface;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use LaborDigital\T3ba\Tool\Tca\Preview\PreviewLinkHook;

class PreviewLinkStep implements TcaPostProcessorStepInterface, NoDiInterface
{
    public const CONFIG_KEY = 'tablePreviewLink';
    
    /**
     * @inheritDoc
     */
    public function process(string $tableName, array &$config, array &$meta): void
    {
        if (! isset($config['ctrl'][static::CONFIG_KEY]) || ! is_array($config['ctrl'][static::CONFIG_KEY])) {
            return;
        }
        
        $meta['tsConfig'] = ($meta['tsConfig'] ?? '') . PHP_EOL .
                            $this->buildPreviewLinkTsConfig(
                                $tableName, $config['ctrl'][static::CONFIG_KEY]
                            );
        
        unset($config['ctrl'][static::CONFIG_KEY]);
    }
    
    /**
     * Generates the ts config to pass the required information to our preview link hook
     *
     * @param   string  $tableName
     * @param   array   $configuration
     *
     * @return string
     */
    protected function buildPreviewLinkTsConfig(string $tableName, array $configuration): string
    {
        $hiddenField = $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled'] ?? 'hidden';
        
        return '
        TCEMAIN.preview {
            ' . $tableName . ' {
                previewPageId = 0
                fieldToParameterMap {
                    uid = ' . PreviewLinkHook::UID_TRANSFER_MARKER . '
                    ' . $hiddenField . ' = ' . PreviewLinkHook::HIDDEN_TRANSFER_MARKER . '
                }
                additionalGetParameters {
                    ' . PreviewLinkHook::TABLE_TRANSFER_MARKER . ' = ' . $tableName . '
                    ' . PreviewLinkHook::CONFIG_TRANSFER_MARKER . ' = ' .
               base64_encode(SerializerUtil::serializeJson($configuration)) . '
                }
            }
        }';
    }
}