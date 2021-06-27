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
 * Last modified: 2021.06.25 at 21:36
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Rendering\Renderer;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\Fal\FalService;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3ba\Tool\Translation\Translator;
use Neunerlei\Inflection\Inflector;
use Throwable;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;

class FieldRenderer implements PublicServiceInterface
{
    use ContainerAwareTrait;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Translation\Translator
     */
    protected $translator;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Fal\FalService
     */
    protected $falService;
    
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
     * @param   bool    $textOnly   By default the rendered value may contain HTML markup, if you set this
     *                              flag to true those cases will be replaced with a textual representation
     *
     * @return string|null
     */
    public function render(string $tableName, string $fieldName, array $row, bool $textOnly = false): ?string
    {
        $fieldTca = $GLOBALS['TCA'][$tableName]['columns'][$fieldName] ?? [];
        
        if (empty($fieldTca) || (empty($row[$fieldName]) && $row[$fieldName] !== 0)) {
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
        
        $content = $this->htmlEncode(BackendUtility::getProcessedValue($tableName, $fieldName, $row[$fieldName]));
        
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
     */
    protected function renderFileField(string $tableName, array $fieldTca, string $field, array $row): string
    {
        $matchField = $fieldTca['config']['foreign_match_fields']['fieldname'] ?? $field;
        $files = $this->falService->getFile($row['uid'], $tableName, $matchField, false);
        $maxItems = $fieldTca['config']['maxItems'] ?? 1;
        
        $content = [];
        foreach ($files as $c => $file) {
            if ($c > $maxItems) {
                break;
            }
            
            $info = $this->falService->getFileInfo($file);
            if ($info->isImage()) {
                $width = $info->getImageInfo() ? min(max($info->getImageInfo()->getWidth(), 50), 200) : 200;
                $content[] = '<img src="' .
                             $this->htmlEncode($this->falService->getResizedImageUrl($file, ['maxWidth' => $width, 'relative'])) .
                             '" style="width:100%; max-width:' . $width . 'px;"' .
                             ' title="' . $this->htmlEncode($info->getFileName()) . '"' .
                             ' alt="' . ($info->getImageInfo()->getAlt() ?? $info->getFileName()) . '"/>';
            } else {
                $content[] = $this->htmlEncode($info->getFileName());
            }
        }
        
        if (empty($content)) {
            return '&nbsp;';
        }
        
        if (count($content) === 1) {
            return reset($content);
        }
        
        return implode('&nbsp;', $content);
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
     */
    protected function renderFileFieldText(string $tableName, array $fieldTca, string $field, array $row): string
    {
        $matchField = $fieldTca['config']['foreign_match_fields']['fieldname'] ?? $field;
        $files = $this->falService->getFile($row['uid'], $tableName, $matchField, false);
        $maxItems = $fieldTca['config']['maxItems'] ?? 1;
        
        
        $content = [];
        foreach ($files as $c => $file) {
            if ($c === $maxItems) {
                break;
            }
            
            $content[] = $file->getNameWithoutExtension() . ' [' . $file->getUid() . ']';
        }
        
        return implode(',', $content);
    }
    
    /**
     * Renders a typo link value as somewhat readable link
     *
     * @param   string  $value
     *
     * @return string
     */
    protected function renderLinkField(string $value): string
    {
        try {
            $linkData = $this->makeInstance(LinkService::class)->resolve(
                $this->makeInstance(TypoLinkCodecService::class)->decode($value)['url'] ?? ''
            );
        } catch (Throwable $exception) {
            return $value;
        }
        
        if (empty($linkData['type'])) {
            return $value;
        }
        
        switch ($linkData['type']) {
            case LinkService::TYPE_PAGE:
                $record = BackendUtility::readPageAccess($linkData['pageuid'], '1=1');
                if (! empty($record['uid'])) {
                    return $record['_thePathFull'] . '[' . $record['uid'] . ']';
                }
                
                return $value;
            case LinkService::TYPE_EMAIL:
                return $linkData['email'] ?? $value;
            case LinkService::TYPE_URL:
                return $linkData['url'] ?? $value;
            case LinkService::TYPE_FILE:
                if (! empty($linkData['file'])) {
                    $linkData['file']->getNameWithoutExtension() . ' [' . $linkData['file']->getUid() . ']';
                }
                
                return $value;
            case LinkService::TYPE_FOLDER:
                if (! empty($linkData['folder'])) {
                    $linkData['folder']->getPublicUrl();
                }
                
                return $value;
            case LinkService::TYPE_RECORD:
            case 'linkSetRecord':
                $tableName = $this->cs()->ts->getTsConfig([
                    'TCEMAIN',
                    'linkHandler',
                    $linkData['identifier'] ?? '',
                    'configuration',
                    'table',
                ]);
                if (! empty($tableName)) {
                    $tableName = NamingUtil::resolveTableName($tableName);
                    $record = BackendUtility::getRecord($tableName, $linkData['uid']);
                    if ($record) {
                        $recordTitle = BackendUtility::getRecordTitle($tableName, $record);
                        $tableTitle = $this->translator->translate($GLOBALS['TCA'][$tableName]['ctrl']['title'] ??
                                                                   $tableName);
                        
                        return sprintf('%s [%s:%d]', $recordTitle, $tableTitle, $linkData['uid']);
                    }
                }
                
                return $value;
            case LinkService::TYPE_TELEPHONE:
                return $linkData['telephone'] ?? $value;
        }
        
        return $value;
    }
    
    /**
     * Helper to encode html special characters
     *
     * @param $value
     *
     * @return string
     */
    protected function htmlEncode($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5);
    }
}
