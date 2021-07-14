<?php
/**
 * Copyright 2020 LABOR.digital
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
 * Last modified: 2020.03.20 at 12:34
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\Table;

use LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use LaborDigital\Typo3BetterApi\Frontend\TablePreview\PreviewLinkHook;
use TYPO3\CMS\Core\SingletonInterface;

class TableConfigGenerator implements SingletonInterface
{
    use ExtBasePersistenceMapperTrait;

    /**
     * Holds the additional config data for all tables we generated the config for.
     * This is used to hold the data until it is injected into table config object
     *
     * @var array
     */
    protected $additionalConfig = [];

    /**
     * Generates the TCA list for the given stack of Table configuration classes
     *
     * @param   array             $stack       The configuration class list to iterate over
     * @param   ExtConfigContext  $context     The ext config context instance
     * @param   bool              $isOverride  True if the given stack represents the table override configurations
     *
     * @return array
     */
    public function generateTableTcaList(array $stack, ExtConfigContext $context, bool $isOverride): array
    {
        // Prepare the tca output
        $tcaList = [];

        // Run through the stack
        foreach ($stack as $tableName => $data) {
            $context->runWithFirstCachedValueDataScope($data,
                function () use ($data, $tableName, $context, $isOverride, &$tcaList) {
                    // Create the table instance
                    $table = TcaTable::makeInstance($tableName, $context);

                    // Run through table stack
                    $context->runWithCachedValueDataScope($data,
                        function (string $configClass) use ($tableName, $context, $table, $isOverride) {
                            // Validate configuration
                            if (! class_exists($configClass)) {
                                throw new ExtConfigException('Could not load table configuration for table: '
                                                             . $tableName . ' because class: ' . $configClass
                                                             . ' does not exist!');
                            }
                            if (! in_array(TableConfigurationInterface::class, class_implements($configClass))) {
                                throw new ExtConfigException('Could not load table configuration for table: '
                                                             . $tableName . ' because class: ' . $configClass
                                                             . ' does not implement the required interface: '
                                                             . TableConfigurationInterface::class . '!');
                            }

                            // Run the configuration class
                            call_user_func([$configClass, 'configureTable'], $table, $context, $isOverride);
                        });

                    // Build the tca for this table
                    $tcaList[$tableName]                = $tca = $table->__build();
                    $this->additionalConfig[$tableName] = $tca['additionalConfig'];
                });
        }

        // Done
        return $tcaList;
    }

    /**
     * Should be called after both the default TCA and the TCA override stack ran.
     * This will build the cachable table configuration object and return it.
     *
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext  $context
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Table\TableConfig
     */
    public function generateTableConfig(ExtConfigContext $context): TableConfig
    {
        $config = $context->getInstanceOf(TableConfig::class);

        // Build the sql string
        $config->sql = $context->SqlGenerator->getFullSql();
        $context->SqlGenerator->flush();

        // Build additional config
        $typoScript = $tableListPositions = [];
        foreach ($this->additionalConfig as $tableName => $additionalConfig) {
            // Build extbase model mapping
            $typoScript[] = $this->getPersistenceTs($additionalConfig['modelList'], $tableName);

            // Build list of pages that are allowed on standard pages
            if ($additionalConfig['allowOnStandardPages']) {
                $config->tablesOnStandardPages[] = $tableName;
            }

            // Add list position definitions
            if (! empty($additionalConfig['listPosition'])) {
                $tableListPositions[$tableName] = $additionalConfig['listPosition'];
            }

            if (! empty($additionalConfig['previewLink'])) {
                $config->tsConfig
                    .= PHP_EOL . $this->generatePreviewLinkTsConfig($tableName, $additionalConfig['previewLink']);
            }
        }

        // Combine the config
        $config->typoScript         = implode(PHP_EOL, array_filter($typoScript));
        $config->tableListPositions = $tableListPositions;

        // Done
        return $config;
    }

    protected function generatePreviewLinkTsConfig(string $tableName, array $configuration): string
    {
        $hiddenField = $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled'] ?? 'hidden';
        
        return '
        TCEMAIN.preview {
            ' . $tableName . ' {
                previewPageId = -1
                fieldToParameterMap {
                    uid = ' . PreviewLinkHook::UID_TRANSFER_MARKER . '
                    ' . $hiddenField . ' = ' . PreviewLinkHook::HIDDEN_TRANSFER_MARKER . '
                }
                additionalGetParameters {
                    ' . PreviewLinkHook::TABLE_TRANSFER_MARKER . ' = ' . $tableName . '
                    ' . PreviewLinkHook::CONFIG_TRANSFER_MARKER . ' = '
               . base64_encode(\GuzzleHttp\json_encode($configuration)) . '
                }
            }
        }';
    }
}
