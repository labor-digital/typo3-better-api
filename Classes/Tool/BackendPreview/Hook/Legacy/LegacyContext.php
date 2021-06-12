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
 * Last modified: 2021.06.11 at 21:45
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\BackendPreview\Hook\Legacy;


use LaborDigital\T3ba\Core\Di\StaticContainerAwareTrait;
use LaborDigital\T3ba\Event\BackendPreview\LegacyPreviewRenderingEvent;
use LaborDigital\T3ba\Tool\BackendPreview\Hook\BackendPreviewUtils;
use LaborDigital\T3ba\Tool\BackendPreview\Renderer\FieldListRenderer;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use TYPO3\CMS\Backend\View\PageLayoutView;

/**
 * Class LegacyContext
 *
 * Legacy helper to hook the new backend preview renderer in the old hook based api
 *
 * @package LaborDigital\T3ba\Tool\BackendPreview\Hook\Legacy
 * @deprecated
 */
class LegacyContext
{
    use StaticContainerAwareTrait;
    
    /**
     * A list of all rendering events that have been dispatched
     *
     * @var array
     */
    protected static $events = [];
    
    /**
     * Contains the plugin variant map after it was loaded once
     *
     * @var array
     */
    protected static $pluginVariantMap;
    
    /**
     * The list of resolved preview renderers
     */
    protected static $definitions;
    
    /**
     * Returns true if we can handle the given row
     *
     * @param   array  $row
     *
     * @return bool
     */
    public static function canHandle(array $row): bool
    {
        if (! is_array(static::$definitions)) {
            static::$definitions = static::cs()->typoContext->config()->getConfigValue('t3ba.backendPreview.previewRenderers', []);
        }
        
        foreach (static::$definitions as $def) {
            [, $constraints] = $def;
            
            // Non-empty constraints in form of an array that don't match the row -> skip
            if (! empty($constraints) && is_array($constraints)
                && count(array_intersect_assoc($constraints, $row)) !== count($constraints)) {
                continue;
            }
            
            return true;
        }
        
        return false;
    }
    
    public static function getEvent(array $row, PageLayoutView $pageLayoutView, $headerContent, $itemContent): LegacyPreviewRenderingEvent
    {
        $uid = $row['uid'] ?? null;
        
        if ($uid === null) {
            throw new \InvalidArgumentException('The row must contain a uid!');
        }
        
        if (isset(static::$events[$uid])) {
            return static::$events[$uid];
        }
        
        static::cs()->eventBus->dispatch(static::$events[$uid] = new LegacyPreviewRenderingEvent(
            $row,
            static::makeUtilsInstance($row, $pageLayoutView, $headerContent, $itemContent),
            static::getPluginVariant($row)
        ));
        
        return static::$events[$uid];
    }
    
    /**
     * Generates the backend preview utils instance for the given item by linking
     * the internal methods to a public helper class.
     *
     * @param   \TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem  $item
     *
     * @return \LaborDigital\T3ba\Tool\BackendPreview\Hook\BackendPreviewUtils
     */
    protected static function makeUtilsInstance(array $row, PageLayoutView $pageLayoutView, $headerContent, $itemContent): BackendPreviewUtils
    {
        return static::makeInstance(BackendPreviewUtils::class, [
            [
                'renderDefaultHeader' => function () use ($row, $headerContent) {
                    if (! empty($headerContent)) {
                        return $headerContent;
                    }
                    
                    return static::findDefaultHeader($row);
                },
                'renderDefaultContent' => function () use ($itemContent) {
                    return $itemContent;
                },
                'renderDefaultFooter' => function () use ($row) {
                    return static::findDefaultFooter($row);
                },
                'renderFieldList' => function (array $fields, ?string $tableName) use ($row) {
                    return static::getService(FieldListRenderer::class)->render(
                        $tableName ?? 'tt_content', $row, $fields
                    );
                },
                'wrapWithEditLink' => function ($linkText) use ($pageLayoutView, $row) {
                    return $pageLayoutView->linkEditContent($linkText, $row);
                },
            ],
        ]);
    }
    
    /**
     * Resolves the plugin/content element variant that was registered for this item.
     * It will return null if the default variant is used or no variant was found -> meaning the same
     *
     * @param   array  $row
     *
     * @return string|null
     */
    protected static function getPluginVariant(array $row): ?string
    {
        if (! isset(static::$pluginVariantMap)) {
            $variants = static::cs()->typoContext->config()->getConfigValue('typo.extBase.element.variants');
            if (! empty($variants)) {
                $variants = SerializerUtil::unserializeJson($variants);
            }
            static::$pluginVariantMap = $variants ?? [];
        }
        
        return static::$pluginVariantMap[$row['list_type'] ?? '-1']
               ?? static::$pluginVariantMap[$row['CType']]
                  ?? null;
    }
    
    protected static function findDefaultHeader(array $row): string
    {
        // Find for plugin
        if ($row['CType'] === 'list') {
            $signature = $row['list_type'];
            foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $listTypeItem) {
                if ($listTypeItem[1] !== $signature) {
                    continue;
                }
                
                return static::cs()->translator->translateBe($listTypeItem[0]);
            }
            
            return '';
        }
        
        // Find for content element
        $signature = $row['CType'];
        foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $listTypeItem) {
            if ($listTypeItem[1] !== $signature) {
                continue;
            }
            
            return static::cs()->translator->translateBe($listTypeItem[0]);
        }
        
        return '';
    }
    
    protected static function findDefaultFooter(array $row): string
    {
        $info[] = static::getService(FieldListRenderer::class)->render(
            'tt_content', $row, ['starttime', 'endtime', 'fe_group', 'space_before_class', 'space_after_class']
        );
        if (! empty($GLOBALS['TCA']['tt_content']['ctrl']['descriptionColumn']) && ! empty($record[$GLOBALS['TCA']['tt_content']['ctrl']['descriptionColumn']])) {
            $info[] = htmlspecialchars($row[$GLOBALS['TCA']['tt_content']['ctrl']['descriptionColumn']]);
        }
        
        if (! empty($info)) {
            $content = implode('<br>', $info);
        }
        
        if (! empty($content)) {
            $content = '<div class="t3-page-ce-footer">' . $content . '</div>';
        }
        
        return $content;
    }
}