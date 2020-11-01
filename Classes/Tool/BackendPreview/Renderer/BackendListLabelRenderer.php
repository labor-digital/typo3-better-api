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
 * Last modified: 2020.10.20 at 09:57
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\BackendPreview\Renderer;


use LaborDigital\T3BA\Event\BackendPreview\ListLabelRenderingEvent;
use LaborDigital\T3BA\Tool\BackendPreview\BackendListLabelRendererInterface;
use LaborDigital\T3BA\Tool\BackendPreview\BackendPreviewException;
use Throwable;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class BackendListLabelRenderer extends AbstractRenderer
{
    /**
     * Tries to render the backend list label of a specific content element based on the data provided
     * in the given preview rendering event
     *
     * @param   \LaborDigital\T3BA\Event\BackendPreview\ListLabelRenderingEvent  $event
     */
    public function render(ListLabelRenderingEvent $event): void
    {
        $title      = $this->findDefaultHeader($event->getRow());
        $foundLabel = false;
        foreach ($this->TypoContext()->Config()->getConfigValue('t3ba.backendPreview.listLabelRenderers', []) as $def) {
            [$handler, $constraints] = $def;

            // Non-empty constraints in form of an array that don't match the row -> skip
            if (! empty($constraints) && is_array($constraints)
                && count(array_intersect_assoc($constraints, $event->getRow())) !== count($constraints)) {
                continue;
            }

            $foundLabel = true;
            $title      .= is_array($handler)
                ? $this->renderColumns($handler, $event->getRow())
                : $this->callConcreteRenderer($handler, $event);
        }

        if (! $foundLabel) {
            $title .= $this->renderFallbackLabel($event->getRow());
        }

        $event->setTitle($title);
    }

    /**
     * Internal helper to call the backend list renderer class for the given row.
     * It will return the rendered label string that we should append to the title.
     *
     * @param   string                   $rendererClass
     * @param   ListLabelRenderingEvent  $event
     */
    protected function callConcreteRenderer(string $rendererClass, ListLabelRenderingEvent $event): string
    {
        try {
            // Check if the renderer class is valid
            if (! class_exists($rendererClass)) {
                throw new BackendPreviewException("The given renderer class: $rendererClass does not exist!");
            }

            $renderer = $this->getInstanceOf($rendererClass);

            if (! $renderer instanceof BackendListLabelRendererInterface) {
                throw new BackendPreviewException(
                    "The given renderer class: $rendererClass has to implement the correct interface: "
                    . BackendListLabelRendererInterface::class);
            }

            return ' | ' . $renderer->renderBackendListLabel($event->getRow(), $event->getOptions());
        } catch (Throwable $e) {
            return '[ERROR]: ' . $this->stringifyThrowable($e);
        }
    }

    /**
     * Renders a list of selected columns as concatenated string
     *
     * @param   array          $columns           the list of columns to render
     * @param   array          $row               The database row to extract the columns from
     * @param   callable|null  $additionalFilter  An optional filter to remove fields on the fly.
     *                                            The callable must return a boolean: True to keep the value,
     *                                            false to remove it!
     *
     * @return string
     */
    protected function renderColumns(array $columns, array $row, ?callable $additionalFilter = null): string
    {
        $result = [];
        foreach ($columns as $column) {
            $value = trim(strip_tags((string)$row[$column] ?? ''));

            if ($additionalFilter !== null && ! $additionalFilter($value) || empty($value)) {
                continue;
            }

            if ($column === 'tstamp' || $column === 'crdate') {
                try {
                    $value = BackendUtility::date($value);
                } catch (Throwable $e) {
                    // Silence
                }
            } else {
                $value = BackendUtility::getProcessedValue('tt_content', $column, $value);
            }

            $result[] = $this->sliceFieldContent($value);
        }

        if (empty($result)) {
            return '';
        }

        return ' | ' . implode(' | ', $result);
    }

    /**
     * Renders an automatic fallback label based on the most commonly used columns of the tt_content table
     *
     * @param   array  $row
     *
     * @return string
     */
    protected function renderFallbackLabel(array $row): string
    {
        $isRendered = false;

        return $this->renderColumns(['headline', 'title', 'header', 'bodytext', 'content', 'description', 'desc'],
            $row, static function (string $value) use (&$isRendered) {
                if ($isRendered) {
                    return false;
                }

                return $isRendered = ! empty($value) && ! is_numeric($value);
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
