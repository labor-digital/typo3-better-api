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


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\ConfigBuilder;


use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator;
use LaborDigital\T3ba\Tool\BackendPreview\Hook\ContentPreviewRenderer;
use Neunerlei\Arrays\Arrays;

class BackendPreviewBuilder
{
    
    /**
     * Generates the list of backend preview and list label renderers based on the configurator
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     *
     * @return array
     */
    public static function buildRendererList(
        AbstractElementConfigurator $configurator
    ): array
    {
        $renderers = [];
        
        // This loop registers the preview and list label renderers for T3fa
        foreach (
            [
                'preview' => 'getBackendPreviewRenderer',
                'listLabel' => 'getBackendListLabelRenderer',
            ] as $key => $method
        ) {
            $renderer = $configurator->$method();
            if (! empty($renderer)) {
                $renderers[$key][] = [$renderer, $configurator->getFieldConstraints()];
            }
        }
        
        return array_filter($renderers);
    }
    
    /**
     * Merges two results of buildRendererList() into a single array
     *
     * @param   array  $listA
     * @param   array  $listB
     *
     * @return array
     */
    public static function mergeRendererList(array $listA, array $listB): array
    {
        return Arrays::merge($listA, $listB, 'nn');
    }
    
    /**
     * Registers the preview renderer hook into the given list
     *
     * @param   array        $list
     * @param   string       $cType
     * @param   string|null  $pluginSignature
     *
     * @return array
     */
    public static function addHookToList(array $list, string $cType, ?string $pluginSignature = null): array
    {
        if ($pluginSignature === null) {
            $list['types'][$cType]['previewRenderer'] = ContentPreviewRenderer::class;
        } else {
            $list['types'][$cType]['previewRenderer'][$pluginSignature] = ContentPreviewRenderer::class;
        }
        
        return $list;
    }
}