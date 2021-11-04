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
 * Last modified: 2021.11.04 at 16:03
 */

declare(strict_types=1);

namespace LaborDigital\T3ba\FormEngine\Wizard;

use LaborDigital\T3ba\Tool\FormEngine\Custom\Wizard\AbstractCustomWizard;
use LaborDigital\T3ba\Tool\Rendering\Renderer\FieldListRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileLinkPreviewWizard extends AbstractCustomWizard
{
    
    /**
     * @inheritDoc
     */
    public function render(): string
    {
        if (empty($this->context->getValue())) {
            return '';
        }
        
        return $this->renderFilePreview((string)$this->context->getValue());
    }
    
    protected function renderFilePreview(string $link): string
    {
        try {
            $fileInfo = $this->cs()->fal->getFileInfo($link);
        } catch (\Throwable $e) {
            if (stripos($e->getMessage(), 'No such file or directory') !== false) {
                return $this->htmlEncode($link . ' (Missing File)');
            }
            
            return $this->htmlEncode($link) . ' | Error while rendering: ' . $e->getMessage();
        }
        
        $file = $fileInfo->getFile();
        
        $html = '';
        
        if ($fileInfo->isImage()) {
            $previewWidth = 150;
            
            $linkAttributes = GeneralUtility::implodeAttributes([
                'data-dispatch-action' => 'TYPO3.InfoWindow.showItem',
                'data-dispatch-args-list' => '_FILE,' . (int)$file->getUid(),
            ], true);
            
            /** @noinspection NullPointerExceptionInspection */
            $html .= '<table class="table" style="background:#fff;margin-bottom:0">' .
                     '<tbody><tr><td style="text-align:center;padding: 10px">' .
                     '<a href="#" ' . $linkAttributes . '>' .
                     '<img src="' .
                     $this->htmlEncode($this->cs()->fal->getResizedImageUrl($file, ['maxWidth' => $previewWidth, 'relative'])) .
                     '" style="width:100%; max-width:' . $previewWidth . 'px; max-height: 150px"' .
                     ' title="' . $this->htmlEncode($fileInfo->getFileName()) . '"' .
                     ' alt="' . ($fileInfo->getImageInfo()->getAlt() ?? $fileInfo->getFileName()) . '"/>' .
                     '</a>' .
                     '</td></tr></tbody></table>';
        }
        
        $renderer = $this->getService(FieldListRenderer::class);
        $html .= $renderer->render('sys_file_reference', $file->getProperties(), ['title', 'description', 'alternative']);
        
        return $html;
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