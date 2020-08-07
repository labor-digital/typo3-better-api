<?php
declare(strict_types=1);
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

namespace LaborDigital\Typo3BetterApi\BackendPreview;

interface BackendPreviewServiceInterface
{

    /**
     * Can be used to register a backend preview renderer for any kind of tt_content element in the page module.
     *
     * There can only be a single renderer per $fieldConstraints. If you define different renderers with the
     * same $fieldConstraints the first one will be overwritten by the second one.
     *
     * @param   string  $rendererClass     The renderer class that has to implement the
     *                                     BackendPreviewRendererInterface.
     * @param   array   $fieldConstraints  These constraints are an array of field keys and values that have to
     *                                     match in a tt_content row in order for this service to call the renderer
     *                                     class.
     *
     *                                 As an example: If you have a plugin with a signature "mxext_myplugin"
     *                                 your constraints should look like: ["CType" => "list", "list_type" =>
     *                                 "mxext_myplugin"]. If you want a renderer for a content element just set the
     *                                 CType ["CType" => "mxext_myplugin"]. If you want to watch for any other value or
     *                                 combination of values... feel free to be creative... All given fields and values
     *                                 are seen as "AND" constraints
     * @param   bool    $override          If you set this to true the preview will renderer will be executed even if
     *                                     the preview was already rendered by other means.
     *
     * @return \LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewService
     * @see \LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewRendererInterface
     */
    public function registerBackendPreviewRenderer(
        string $rendererClass,
        array $fieldConstraints,
        bool $override = false
    ): BackendPreviewService;

    /**
     * Can be used to register a backend list label renderer for any kind of tt_content element in the list module.
     *
     * There can only be a single renderer per $fieldConstraints. If you define different renderers with the
     * same $fieldConstraints the first one will be overwritten by the second one.
     *
     * @param   string|array  $rendererClassOrColumns   Either the renderer class that implements
     *                                                  BackendListLabelRendererInterface or an array of field names
     *                                                  that should be used to automatically generate the list label
     * @param   array         $fieldConstraints         These constraints are an array of field keys and values that
     *                                                  have to match in a tt_content row in order for this service to
     *                                                  call the renderer class. See registerBackendPreviewRenderer()
     *                                                  for further details
     *
     * @return \LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewService
     * @throws \LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewException
     * @see \LaborDigital\Typo3BetterApi\BackendPreview\BackendListLabelRendererInterface
     */
    public function registerBackendListLabelRenderer(
        $rendererClassOrColumns,
        array $fieldConstraints
    ): BackendPreviewService;
}
