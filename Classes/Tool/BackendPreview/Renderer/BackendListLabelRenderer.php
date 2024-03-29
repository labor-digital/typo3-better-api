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


namespace LaborDigital\T3ba\Tool\BackendPreview\Renderer;


use LaborDigital\T3ba\Event\BackendPreview\ContentListLabelRenderingEvent;
use LaborDigital\T3ba\Event\BackendPreview\ListLabelRenderingEvent;
use LaborDigital\T3ba\Event\BackendPreview\TableListLabelRenderingEvent;
use LaborDigital\T3ba\Tool\BackendPreview\BackendListLabelRendererInterface;
use LaborDigital\T3ba\Tool\BackendPreview\BackendPreviewException;
use LaborDigital\T3ba\Tool\Rendering\Renderer\FieldRenderer;
use LaborDigital\T3ba\Tool\Tca\ContentType\ContentTypeUtil;
use LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ContentRepository;
use LaborDigital\T3ba\Tool\Tca\TcaUtil;
use Throwable;

class BackendListLabelRenderer extends AbstractRenderer
{
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ContentRepository
     */
    protected $contentRepository;
    
    /**
     * @var FieldRenderer
     */
    protected $fieldRenderer;
    
    /**
     * BackendListLabelRenderer constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ContentRepository  $contentRepository
     * @param   \LaborDigital\T3ba\Tool\Rendering\Renderer\FieldRenderer          $fieldRenderer
     */
    public function __construct(ContentRepository $contentRepository, FieldRenderer $fieldRenderer)
    {
        $this->contentRepository = $contentRepository;
        $this->fieldRenderer = $fieldRenderer;
    }
    
    /**
     * Tries to render the backend list label of a specific content element based on the data provided
     * in the given preview rendering event
     *
     * @param   \LaborDigital\T3ba\Event\BackendPreview\ListLabelRenderingEvent  $event
     *
     * @deprecated will be removed in v11 in favour of renderForContent and renderForTable
     */
    public function render(ListLabelRenderingEvent $event): void
    {
        $this->renderForContent($event);
    }
    
    /**
     * Renders the list label for tt_content elements
     *
     * @param   \LaborDigital\T3ba\Event\BackendPreview\ContentListLabelRenderingEvent  $event
     */
    public function renderForContent(ContentListLabelRenderingEvent $event): void
    {
        TcaUtil::runWithResolvedTypeTca($event->getRow(), $event->getTableName(), function () use ($event) {
            $row = array_map(static function ($v) { return is_array($v) ? reset($v) : $v; }, $event->getRow());
            $title = $this->findDefaultHeader($row);
            $foundLabel = false;
            
            foreach (
                $this->getTypoContext()->config()->getConfigValue('t3ba.backendPreview.listLabelRenderers', []) as $def
            ) {
                [$handler, $constraints] = $def;
                
                // Non-empty constraints in form of an array that don't match the row -> skip
                if (! empty($constraints) && is_array($constraints)
                    && count(array_intersect_assoc($constraints, $row)) !== count($constraints)) {
                    continue;
                }
                
                $foundLabel = true;
                $title .= is_array($handler)
                    ? $this->renderColumns($handler, $event->getTableName(), $row)
                    : $this->callConcreteRenderer($handler, $event->getOptions(), $event->getTableName(), $row);
                break;
            }
            
            if (! $foundLabel) {
                $title .= $this->renderContentFallbackLabel($event->getTableName(), $row);
            }
            
            $event->setTitle($title);
        });
    }
    
    /**
     * Renders the list label for generic table rows
     *
     * @param   \LaborDigital\T3ba\Event\BackendPreview\TableListLabelRenderingEvent  $event
     */
    public function renderForTable(TableListLabelRenderingEvent $event): void
    {
        TcaUtil::runWithResolvedTypeTca($event->getRow(), $event->getTableName(), function () use ($event) {
            $row = array_map(static function ($v) { return is_array($v) ? reset($v) : $v; }, $event->getRow());
            
            $title = $this->callConcreteRenderer($event->getHandler(), $event->getOptions(), $event->getTableName(), $row);
            $event->setTitle($title);
        });
    }
    
    /**
     * Internal helper to call the backend list renderer class for the given row.
     * It will return the rendered label string that we should append to the title.
     *
     * @param   string  $rendererClass
     * @param   array   $options
     * @param   array   $row
     *
     * @return string
     */
    protected function callConcreteRenderer(string $rendererClass, array $options, string $tableName, array $row): string
    {
        try {
            if (! class_exists($rendererClass)) {
                throw new BackendPreviewException("The given renderer class: $rendererClass does not exist!");
            }
            
            $renderer = $this->makeInstance($rendererClass);
            
            if (! $renderer instanceof BackendListLabelRendererInterface) {
                throw new BackendPreviewException(
                    "The given renderer class: $rendererClass has to implement the correct interface: "
                    . BackendListLabelRendererInterface::class);
            }
            
            return ContentTypeUtil::runWithRemappedTca($row, function () use ($renderer, $options, $row, $tableName) {
                return ' ' . $renderer->renderBackendListLabel(
                        $this->contentRepository->getExtendedRow($row),
                        $options,
                        $tableName
                    );
            });
            
        } catch (Throwable $e) {
            return '[ERROR]: ' . $this->stringifyThrowable($e);
        }
    }
    
    /**
     * Renders a list of selected columns as concatenated string
     *
     * @param   array          $columns                             the list of columns to render
     * @param   string         $tableName                           The name of the table to render the columns for
     * @param   array          $row                                 The prepared database row to render the label for
     * @param   callable|null  $additionalFilter                    An optional filter to remove fields on the fly.
     *                                                              The callable must return a boolean: True to keep the value,
     *                                                              false to remove it!
     *
     * @return string
     */
    public function renderColumns(
        array $columns,
        string $tableName,
        array $row,
        ?callable $additionalFilter = null
    ): string
    {
        $row = $this->contentRepository->getExtendedRow($row);
        
        return ContentTypeUtil::runWithRemappedTca($row, function () use ($columns, $row, $additionalFilter, $tableName) {
            $result = [];
            foreach ($columns as $column) {
                $value = trim(strip_tags((string)$row[$column]));
                
                if (empty($value) || ($additionalFilter !== null && ! $additionalFilter($value))) {
                    continue;
                }
                
                $result[] = $this->sliceFieldContent(
                    $this->fieldRenderer->render($tableName, $column, $row, true) ?? ''
                );
                
            }
            $result = array_filter($result);
            
            if (empty($result)) {
                return '';
            }
            
            return ' ' . implode(' | ', $result);
        });
    }
    
    /**
     * Renders an automatic fallback label based on the most commonly used columns of the tt_content table
     *
     * @param   string  $tableName
     * @param   array   $row
     *
     * @return string
     */
    protected function renderContentFallbackLabel(string $tableName, array $row): string
    {
        $isRendered = false;
        
        return $this->renderColumns(['headline', 'title', 'header', 'bodytext', 'content', 'description', 'desc'],
            $tableName, $row, static function (string $value) use (&$isRendered) {
                if ($isRendered) {
                    return false;
                }
                
                return $isRendered = (! empty($value) && ! is_numeric($value));
            });
    }
    
    /**
     * Makes sure that the given value is limited to a number of characters to avoid flooding the list with content
     *
     * @param   string  $value
     *
     * @return string
     */
    protected function sliceFieldContent(string $value): string
    {
        $value = strip_tags($value);
        if (strlen($value) > 100) {
            return trim(substr($value, 0, 100)) . '...';
        }
        
        return $value;
    }
}
