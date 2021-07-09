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
 * Last modified: 2021.07.09 at 10:50
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Rendering\Renderer;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\Tca\ContentType\ContentTypeUtil;
use LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ContentRepository;
use LaborDigital\T3ba\Tool\Tca\TcaUtil;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumn;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Backend\View\PageLayoutContext;

class InlineContentPreviewRenderer implements PublicServiceInterface
{
    use ContainerAwareTrait;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ContentRepository
     */
    protected $contentRepository;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Rendering\Renderer\FieldRenderer
     */
    protected $fieldRenderer;
    
    public function __construct(ContentRepository $contentRepository, FieldRenderer $fieldRenderer)
    {
        $this->contentRepository = $contentRepository;
        $this->fieldRenderer = $fieldRenderer;
    }
    
    /**
     * Renders a backend preview of inline related content elements
     *
     * @param   array   $parentRow
     * @param   string  $inlineField
     *
     * @return string
     * @throws \LaborDigital\T3ba\Tool\Database\BetterQuery\BetterQueryException
     * @see \LaborDigital\T3ba\Tool\Rendering\BackendRenderingService::renderInlineContentPreview()
     */
    public function render(array $parentRow, string $inlineField): string
    {
        return TcaUtil::runWithResolvedTypeTca($parentRow, 'tt_content', function () use ($parentRow, $inlineField) {
            return ContentTypeUtil::runWithRemappedTca($parentRow, function () use ($parentRow, $inlineField) {
                $related = $this->cs()->db->getQuery('tt_content')
                                          ->withWhere(['uid' => $parentRow['uid'] ?? '-1'])
                                          ->getRelated([$inlineField], ['includeHiddenChildren']);
                
                if (empty($related[$inlineField])) {
                    return '';
                }
                
                $column = $this->initializeColumn($parentRow['pid'] ?? 1);
                
                $output = [];
                foreach ($related[$inlineField] as $items) {
                    foreach ($items as $item) {
                        $output[] = $this->renderSingleItem($item->getRow(), $column);
                    }
                }
                
                return $this->renderList($inlineField, $output);
            });
        });
    }
    
    /**
     * Creates a dummy grid column instance we can sue for rendering the items
     *
     * @param   int  $pid
     *
     * @return \TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumn
     */
    protected function initializeColumn(int $pid): GridColumn
    {
        $pageRow = $this->cs()->page->getPageInfo($pid);
        $context = $this->makeInstance(PageLayoutContext::class, [
            $pageRow,
            BackendLayout::create('inline-records', 'Inline', []),
        ]);
        
        return $this->makeInstance(GridColumn::class, [$context, ['colPos' => '-88']]);
    }
    
    /**
     * Renders the HTML preview for a single item in the content list
     *
     * @param   array                                                  $row
     * @param   \TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumn  $column
     *
     * @return string
     */
    protected function renderSingleItem(array $row, GridColumn $column): string
    {
        try {
            $item = $this->makeInstance(GridColumnItem::class, [$column->getContext(), $column, $row]);
            $html = $item->getPreview();
            
            $html = preg_replace('~<a .*?>(.*?)</a>~', '$1', $html);
            
            $style = 'border: 1px solid #ccc; padding: 15px; border-radius: 2px; margin-bottom: 15px';
            if ($item->isDisabled()) {
                $style .= '; opacity:0.5';
            }
            
            return '<div style="' . $style . '">' . $html . '</div>';
        } catch (\Throwable $e) {
            return 'Error while rendering element: ' . $e->getMessage();
        }
    }
    
    /**
     * Renders the final html of the preview list
     *
     * @param   string  $inlineField
     * @param   array   $items
     *
     * @return string
     */
    protected function renderList(string $inlineField, array $items): string
    {
        return '<table class="table">' .
               '<thead>' .
               '<th>' .
               $this->fieldRenderer->renderLabel('tt_content', $inlineField) .
               '</th>' .
               '</thead>' .
               '<tbody>' .
               '<tr><td>' .
               implode($items) .
               '</td></tr>' .
               '</tbody>' .
               '</table>';
    }
    
    /**
     * Internal helper to render a "pretty" error message
     *
     * @param   string  $error
     *
     * @return string
     */
    protected function renderErrorMessage(string $error): string
    {
        return '<div style="background-color:red; padding: 10px; font-family: sans-serif; color: #fff">'
               . htmlentities($error, ENT_QUOTES | ENT_HTML5) . '</div>';
    }
}