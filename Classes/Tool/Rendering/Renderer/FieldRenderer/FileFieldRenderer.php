<?php
/*
 * Copyright 2022 LABOR.digital
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
 * Last modified: 2022.01.31 at 19:57
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Rendering\Renderer\FieldRenderer;


use LaborDigital\T3ba\Tool\Fal\FalService;
use LaborDigital\T3ba\Tool\Fal\FileInfo\FileInfo;
use LaborDigital\T3ba\Tool\Rendering\Renderer\RendererUtilsTrait;
use LaborDigital\T3ba\Tool\Translation\Translator;
use Throwable;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\SingletonInterface;

class FileFieldRenderer implements SingletonInterface
{
    use RendererUtilsTrait;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Fal\FalService
     */
    protected FalService $falService;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Translation\Translator
     */
    protected Translator $translator;
    
    public function __construct(
        FalService $falService,
        Translator $translator
    )
    {
        $this->falService = $falService;
        $this->translator = $translator;
    }
    
    /**
     * Renders the preview of a file field, either as a text only representation, or as html markup including a preview if an image is selected
     *
     * @param   string  $tableName  The name of the database table
     * @param   array   $fieldTca   The TCA configuration array for the column
     * @param   string  $fieldName  The column/field name in the table to render
     * @param   array   $row        The raw database row to extract the value from
     * @param   bool    $textOnly   By default, the rendered value may contain HTML markup, if you set this
     *                              flag to true those cases will be replaced with a textual representation
     *
     * @return string
     */
    public function render(string $tableName, array $fieldTca, string $fieldName, array $row, bool $textOnly = false): string
    {
        return $textOnly
            ? $this->renderFileFieldText($tableName, $fieldTca, $fieldName, $row)
            : $this->renderFileField($tableName, $fieldTca, $fieldName, $row);
    }
    
    /**
     * Renders a single file field with a preview if an image is referenced
     *
     * @param   string  $tableName
     * @param   array   $fieldTca
     * @param   string  $fieldName
     * @param   array   $row
     *
     * @return string
     */
    protected function renderFileField(string $tableName, array $fieldTca, string $fieldName, array $row): string
    {
        return $this->iterateFilesOfField($tableName, $fieldTca, $fieldName, $row,
            function (FileInfo $info, FileReference $file) {
                if ($info->isImage()) {
                    $width = $info->getImageInfo() ? min(max($info->getImageInfo()->getWidth(), 50), 200) : 200;
                    try {
                        return '<img src="' .
                               $this->htmlEncode($this->falService->getResizedImageUrl($file, ['maxWidth' => $width, 'relative'])) .
                               '" style="width:100%; max-width:' . $width . 'px; max-height: 200px"' .
                               ' title="' . $this->htmlEncode($info->getFileName()) . '"' .
                               ' alt="' . ($info->getImageInfo()->getAlt() ?? $info->getFileName()) . '"/>';
                        
                    } catch (Throwable $e) {
                        if (stripos($e->getMessage(), 'No such file or directory') !== false) {
                            return $this->htmlEncode($info->getFileName()) . ' (Missing File)';
                        }
                        
                        return $this->htmlEncode($info->getFileName()) . ' | Error while rendering: ' . $e->getMessage();
                    }
                    
                } else {
                    return $this->htmlEncode($info->getFileName());
                }
            }, static function (array $content, ?string $suffix): string {
                if (empty($content)) {
                    return '&nbsp;';
                }
                
                if (count($content) === 1) {
                    return reset($content);
                }
                
                return implode('&nbsp;', $content) . ($suffix ? '<br><p style="margin-top: 15px"><em>' . $suffix . '</em></p>' : '');
            });
    }
    
    /**
     * Renders a single file field as text only output
     *
     * @param   string  $tableName
     * @param   array   $fieldTca
     * @param   string  $fieldName
     * @param   array   $row
     *
     * @return string
     */
    protected function renderFileFieldText(string $tableName, array $fieldTca, string $fieldName, array $row): string
    {
        return $this->iterateFilesOfField($tableName, $fieldTca, $fieldName, $row,
            function (FileInfo $info, FileReference $file) {
                return $file->getNameWithoutExtension() . ' [' . $file->getUid() . ']';
            }, static function (array $content, ?string $suffix): string {
                return implode(', ', $content) . ($suffix ? ',...' : '');
            });
    }
    
    /**
     * Temporary solution to access methods through the FieldRenderer super class
     *
     * @param   string  $method
     * @param   array   $args
     *
     * @return mixed
     * @deprecated temporary solution until v11
     */
    public function legacyBridge(string $method, array $args)
    {
        switch ($method) {
            case 'renderFileField':
            case 'renderFileFieldText':
            case 'iterateFilesOfField':
                return $this->$method(...$args);
            default:
                throw new \InvalidArgumentException('The method: "' . $method . '" is not supported!');
        }
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
        $matchField = $fieldTca['config']['foreign_match_fields']['fieldname'] ?? $field;
        $files = $this->falService->getFile($row['uid'], $tableName, $matchField, false);
        $maxItems = $fieldTca['config']['maxItems'] ?? 10;
        
        $c = 0;
        $content = [];
        $maxReached = false;
        foreach ($files as $file) {
            if ($c >= $maxItems) {
                $maxReached = true;
                break;
            }
            
            $info = $this->falService->getFileInfo($file);
            if ($info->isHidden()) {
                continue;
            }
            
            $c++;
            
            $res = $callback($info, $file);
            if (! empty($res) && is_string($res)) {
                $content[] = $res;
            }
            unset($res);
        }
        
        return $finisherCallback($content, $maxReached
            ? $this->translator->translateBe('t3ba.tool.renderer.fieldRenderer.imagesNotShown') : null);
    }
}