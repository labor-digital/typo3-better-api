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


namespace LaborDigital\T3ba\Tool\Rendering\Renderer;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\Fal\FalService;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3ba\Tool\Rendering\Renderer\FieldRenderer\FileFieldRenderer;
use LaborDigital\T3ba\Tool\Rendering\Renderer\FieldRenderer\GroupMultiTableRenderer;
use LaborDigital\T3ba\Tool\Rendering\Renderer\FieldRenderer\LinkFieldRenderer;
use LaborDigital\T3ba\Tool\Tca\TcaUtil;
use LaborDigital\T3ba\Tool\Translation\Translator;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\ItemProcessingService;

class FieldRenderer implements PublicServiceInterface
{
    use ContainerAwareTrait;
    use RendererUtilsTrait;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Translation\Translator
     */
    protected $translator;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Fal\FalService
     */
    protected $falService;
    
    /**
     * A list of TCAs that should be restored
     *
     * @var array
     */
    protected $tcaToRestore = [];
    
    public function __construct(Translator $translator, FalService $falService)
    {
        $this->translator = $translator;
        $this->falService = $falService;
    }
    
    /**
     * Renders the title of the table translated for the current user
     *
     * @param   string  $tableName
     *
     * @return string
     */
    public function renderTableTitle(string $tableName): string
    {
        $tableName = NamingUtil::resolveTableName($tableName);
        
        $label = $GLOBALS['TCA'][$tableName]['ctrl']['title'] ?? null;
        if ($label === null) {
            return Inflector::toHuman($tableName);
        }
        
        return $this->translator->translateBe($label);
    }
    
    /**
     * Renders the translated label string given to a field of a table
     *
     * @param   string  $tableName  The name of the database table
     * @param   string  $fieldName  The column/field name in the table to render
     *
     * @return string
     */
    public function renderLabel(string $tableName, string $fieldName): string
    {
        $tableName = NamingUtil::resolveTableName($tableName);
        
        $fieldTca = $GLOBALS['TCA'][$tableName]['columns'][$fieldName] ?? [];
        
        if (isset($fieldTca['label'])) {
            return $this->translator->translateBe($fieldTca['label']);
        }
        
        return Inflector::toHuman($fieldName);
    }
    
    /**
     * Renders the value of a single field in a given row of a database table.
     *
     * @param   string  $tableName  The name of the database table
     * @param   string  $fieldName  The column/field name in the table to render
     * @param   array   $row        The raw database row to extract the value from
     * @param   bool    $textOnly   By default, the rendered value may contain HTML markup, if you set this
     *                              flag to true those cases will be replaced with a textual representation
     *
     * @return string|null
     */
    public function render(string $tableName, string $fieldName, array $row, bool $textOnly = false): ?string
    {
        $tableName = NamingUtil::resolveTableName($tableName);
        
        $fieldTca = $GLOBALS['TCA'][$tableName]['columns'][$fieldName] ?? [];
        
        if (empty($fieldTca) || (empty($row[$fieldName]) && $row[$fieldName] !== 0 && $row[$fieldName] !== '0')) {
            return null;
        }
        
        if ($fieldName === 'sys_language_uid') {
            return $this->cs()->typoContext->language()->getLanguageById($row[$fieldName])->getTitle();
        }
        
        if (isset($row['uid']) && ($fieldTca['config']['foreign_table'] ?? null) === 'sys_file_reference') {
            return $textOnly
                ? $this->renderFileFieldText($tableName, $fieldTca, $fieldName, $row)
                : $this->renderFileField($tableName, $fieldTca, $fieldName, $row);
        }
        
        if (($fieldTca['config']['renderType'] ?? null) === 'inputLink') {
            return $this->renderLinkField((string)$row[$fieldName]);
        }
        
        // @todo remove this in v11 this is already done in TcaUtil::runWithResolvedItemProcFunc()
        if (! empty($fieldTca['config']['itemsProcFunc'])) {
            $this->applyFieldTcaItemProcFunc($tableName, $fieldName, $row);
        }
        
        // This is a special noodle, if we got a group with multiple relation tables,
        // the backend utility expects a comma separated foreign_table, which somehow breaks the dataHandler
        // So we simulate that expected list, by pasting the "allowed" config as "foreign_table" to render the field
        if (isset($row['uid'])
            && $fieldTca['config']['type'] === 'group'
            && $fieldTca['config']['internal_type'] === 'db'
            && ! empty($fieldTca['config']['MM'])
            && empty($fieldTca['config']['foreign_table'])
            && ! empty($fieldTca['config']['allowed'])) {
            return $this->makeInstance(GroupMultiTableRenderer::class)->render($tableName, $fieldName, $row);
        }
        
        $content = TcaUtil::runWithResolvedItemProcFunc($row, $tableName, $fieldName,
            function () use ($tableName, $fieldName, $row) {
                return $this->htmlEncode(
                    BackendUtility::getProcessedValue(
                        $tableName,
                        $fieldName,
                        $row[$fieldName],
                        0,
                        false,
                        false,
                        $row['uid'] ?? 0,
                        true,
                        $row['pid'] ?? 0
                    )
                );
            });
        
        if (empty($content)) {
            $content = $this->htmlEncode($row[$fieldName]);
        }
        
        return $content;
    }
    
    
    /**
     * Renders a single file field with with a preview if an image is referenced
     *
     * @param   string  $tableName
     * @param   array   $fieldTca
     * @param   string  $field
     * @param   array   $row
     *
     * @return string
     * @deprecated will be removed in v11 without replacement
     * @see        FileFieldRenderer
     */
    protected function renderFileField(string $tableName, array $fieldTca, string $field, array $row): string
    {
        return $this->makeInstance(FileFieldRenderer::class)->legacyBridge(__FUNCTION__, func_get_args());
    }
    
    /**
     * Renders a single file field as text only output
     *
     * @param   string  $tableName
     * @param   array   $fieldTca
     * @param   string  $field
     * @param   array   $row
     *
     * @return string
     * @deprecated will be removed in v11 without replacement
     * @see        FileFieldRenderer
     */
    protected function renderFileFieldText(string $tableName, array $fieldTca, string $field, array $row): string
    {
        return $this->makeInstance(FileFieldRenderer::class)->legacyBridge(__FUNCTION__, func_get_args());
    }
    
    /**
     * Internal helper to iterate a list of files for a field reference.
     * It automatically ignores hidden files and handles the "maxItems" definition
     *
     * @param   string    $tableName
     * @param   array     $fieldTca
     * @param   string    $field
     * @param   array     $row
     * @param   callable  $callback          Executed for every file and should return a string value.
     *                                       Receives the $fileInfo and $fileReference as parameters
     * @param   callable  $finisherCallback  Receives the list of results provided by $callback and should combine them to a string
     *
     * @return string
     * @deprecated will be removed in v11 without replacement
     * @see        FileFieldRenderer
     */
    protected function iterateFilesOfField(
        string $tableName,
        array $fieldTca,
        string $field,
        array $row,
        callable $callback,
        callable $finisherCallback
    ): string
    {
        return $this->makeInstance(FileFieldRenderer::class)->legacyBridge(__FUNCTION__, func_get_args());
    }
    
    /**
     * Renders a typo link value as somewhat readable link
     *
     * @param   string  $value
     *
     * @return string
     * @deprecated will be removed in v11, use LinkFieldRenderer directly
     * @see        \LaborDigital\T3ba\Tool\Rendering\Renderer\FieldRenderer\LinkFieldRenderer
     */
    protected function renderLinkField(string $value): string
    {
        return $this->makeInstance(LinkFieldRenderer::class)->render($value);
    }
    
    /**
     * Generates the real values for columns that provide an "itemsProcFunc"
     *
     * @param   string  $tableName
     * @param   string  $fieldName
     * @param   array   $row
     *
     * @deprecated this method will be removed in v11
     * @see        TcaUtil::runWithResolvedItemProcFunc()
     */
    protected function applyFieldTcaItemProcFunc(string $tableName, string $fieldName, array $row): void
    {
        if (isset($this->tcaToRestore[$tableName][$fieldName])) {
            return;
        }
        
        $fieldConf = $GLOBALS['TCA'][$tableName]['columns'][$fieldName] ?? [];
        $this->tcaToRestore[$tableName][$fieldName] = $fieldConf;
        $config = $fieldConf['config'] ?? [];
        $items = $config['items'] ?? [];
        $items = $this->makeInstance(ItemProcessingService::class)->getProcessingItems(
            $tableName,
            0,
            $fieldName,
            $row,
            $config,
            $items
        );
        
        $GLOBALS['TCA'][$tableName]['columns'][$fieldName]['config']['items'] = $items;
    }
}
