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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\BetterController;

use LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewRendererContext;
use LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewRendererInterface;
use LaborDigital\Typo3BetterApi\BackendPreview\ExtBaseBackendPreviewRendererTrait;
use TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException;

/**
 * Class SimpleDefaultActionController
 *
 * Can be used to handle extremely simple extbase plugins that don't require a real controller for themselves.
 *
 * @package LaborDigital\Typo3BetterApi\BetterControllers
 */
abstract class SimpleDefaultActionController extends BetterActionController implements BackendPreviewRendererInterface
{
    use ExtBaseBackendPreviewRendererTrait;
    
    /**
     * Default handler for the main action of this plugin
     */
    public function indexAction()
    {
        // Add data to view
        $this->view->assign('data', $this->data);
        
        // Check if an image has to be loaded
        foreach (['image', 'media', 'image_a', 'image_b'] as $field) {
            if (empty($this->data[$field])) {
                continue;
            }
            $this->view->assign($field,
                $this->FalFiles()->getFile($this->data['uid'], 'tt_content', $field, $field !== 'media'));
        }
    }
    
    /**
     * @inheritDoc
     */
    public function renderBackendPreview(BackendPreviewRendererContext $context)
    {
        try {
            return $this->getFluidView();
        } catch (InvalidTemplateResourceException $e) {
            return '';
        }
    }
}
