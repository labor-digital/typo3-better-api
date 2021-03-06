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

namespace LaborDigital\T3ba\Tool\BackendPreview;

use LaborDigital\T3ba\Core\Di\PublicServiceInterface;

interface BackendPreviewRendererInterface extends PublicServiceInterface
{
    
    /**
     * Should use the given context object to render the backend preview.
     * The body of the preview can either be set into the $context or returned directly as a string.
     * If you set the body AND return a string, the returned value has priority and overrides the set value of the body.
     *
     * @param   BackendPreviewRendererContext  $context
     *
     * @return string|void|\TYPO3Fluid\Fluid\View\ViewInterface|\TYPO3\CMS\Extbase\Mvc\Response
     */
    public function renderBackendPreview(BackendPreviewRendererContext $context);
}
