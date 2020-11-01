<?php
/*
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
 * Last modified: 2020.10.20 at 12:30
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\BackendPreview\Hook;


use LaborDigital\T3BA\Core\DependencyInjection\ContainerAwareTrait;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Event\BackendPreview\PreviewRenderingEvent;
use LaborDigital\T3BA\Tool\Translation\Translator;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;

/**
 * Class ContentPreviewRenderer
 *
 * This class acts as a hook to trigger the backend preview renderer.
 * It can be registered in a type configuration of a TCA (of the tt_content table).
 * It will then trigger the actual preview renderer that has access to the mapped configuration array
 *
 * @package LaborDigital\T3BA\Tool\BackendPreview\Hook
 */
class ContentPreviewRenderer extends StandardContentPreviewRenderer
{
    use ContainerAwareTrait;

    /**
     * @var PreviewRenderingEvent
     */
    protected $event;

    /**
     * @inheritDoc
     */
    public function renderPageModulePreviewHeader(GridColumnItem $item): string
    {
        return (string)$this->getEvent($item)->getHeader();
    }

    /**
     * @inheritDoc
     */
    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        return '<div class="backendPreview">' . $this->getEvent($item)->getBody() . '</div>';
    }

    /**
     * @inheritDoc
     */
    public function renderPageModulePreviewFooter(GridColumnItem $item): string
    {
        return (string)$this->getEvent($item)->getFooter();
    }

    /**
     * Dispatches the render event and stores it so we can provide it for all three render methods
     *
     * @param   \TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem  $item
     *
     * @return \LaborDigital\T3BA\Event\BackendPreview\PreviewRenderingEvent
     */
    protected function getEvent(GridColumnItem $item): PreviewRenderingEvent
    {
        if (isset($this->event)) {
            return $this->event;
        }

        $this->getInstanceOf(TypoEventBus::class)->dispatch($this->event = new PreviewRenderingEvent(
            $item, $this->makeUtilsInstance($item)
        ));

        return $this->event;
    }

    /**
     * Generates the backend preview utils instance for the given item by linking
     * the internal methods to a public helper class.
     *
     * @param   \TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem  $item
     *
     * @return \LaborDigital\T3BA\Tool\BackendPreview\Hook\BackendPreviewUtils
     */
    protected function makeUtilsInstance(GridColumnItem $item): BackendPreviewUtils
    {
        return $this->getWithoutDi(BackendPreviewUtils::class, [
            [
                'renderDefaultHeader'  => function () use ($item) {
                    return parent::renderPageModulePreviewHeader($item);
                },
                'renderDefaultContent' => function () use ($item) {
                    return parent::renderPageModulePreviewContent($item);
                },
                'renderDefaultFooter'  => function () use ($item) {
                    return parent::renderPageModulePreviewFooter($item);
                },
                'renderFieldList'      => function (array $fields) use ($item) {
                    return $this->renderFieldList($item, $fields);
                },
                'wrapWithEditLink'     => function ($linkText) use ($item) {
                    return $this->linkEditContent($linkText, $item->getRecord());
                },
            ],
        ]);
    }

    /**
     * Renders the given list of fields based on the item record
     *
     * @param   \TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem  $item
     * @param   array                                                      $fields
     *
     * @return string
     */
    protected function renderFieldList(GridColumnItem $item, array $fields): string
    {
        $translator = $this->getInstanceOf(Translator::class);
        $data       = $item->getRecord();
        $result     = [];
        foreach ($fields as $field) {
            if (empty($data[$field]) && $data[$field] !== 0) {
                continue;
            }

            $label = Arrays::getPath($GLOBALS, ['TCA', 'tt_content', 'columns', $field, 'label'], $fields);
            // @todo switch this to translateBe when it was implemented
            $label     = $translator->translate($label);
            $processed = BackendUtility::getProcessedValue('tt_content', $field, $data[$field]);
            $result[]  = '<strong>' . htmlspecialchars($translator->translate($label)) . ': </strong> '
                         . htmlspecialchars(empty($processed) ? (string)$data[$field] : $processed);
        }

        return '<ul><li>' . implode('</li><li>', $result) . '</li></ul>';
    }
}
