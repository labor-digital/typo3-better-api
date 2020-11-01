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
 * Last modified: 2020.10.19 at 23:39
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\BackendPreview\Renderer;


use LaborDigital\T3BA\Event\BackendPreview\PreviewRenderingEvent;
use LaborDigital\T3BA\Tool\BackendPreview\BackendPreviewException;
use LaborDigital\T3BA\Tool\BackendPreview\BackendPreviewRendererContext;
use LaborDigital\T3BA\Tool\BackendPreview\BackendPreviewRendererInterface;
use LaborDigital\T3BA\Tool\Simulation\EnvironmentSimulator;
use Neunerlei\Arrays\Arrays;
use Throwable;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Mvc\Response;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class BackendPreviewRenderer extends AbstractRenderer implements SingletonInterface
{
    /**
     * A cache to store the resolved element descriptions by their unique type key
     *
     * @var array
     */
    protected $typeDescriptions;

    /**
     * Tries to render the backend preview of a specific content element based on the data provided
     * in the given preview rendering event
     *
     * @param   \LaborDigital\T3BA\Event\BackendPreview\PreviewRenderingEvent  $event
     */
    public function render(PreviewRenderingEvent $event): void
    {
        foreach ($this->TypoContext()->Config()->getConfigValue('t3ba.backendPreview.previewRenderers', []) as $def) {
            [$handler, $constraints] = $def;

            // Non-empty constraints in form of an array that don't match the row -> skip
            if (! empty($constraints) && is_array($constraints)
                && count(array_intersect_assoc($constraints, $event->getRow())) !== count($constraints)) {
                continue;
            }

            $this->callConcreteRenderer($handler, $event);
            break;
        }
    }

    /**
     * Main handler that receives the rendering class, executes the renderer and updates the event arguments.
     * It has also limited error handling capabilities that catch exceptions and render them as pretty message.
     *
     * @param   string                                                         $rendererClass
     * @param   \LaborDigital\T3BA\Event\BackendPreview\PreviewRenderingEvent  $event
     *
     */
    protected function callConcreteRenderer(string $rendererClass, PreviewRenderingEvent $event): void
    {
        // Check if the renderer class is valid
        if (! class_exists($rendererClass)) {
            throw new BackendPreviewException('The given renderer class: ' . $rendererClass . ' does not exist!');
        }
        $renderer = $this->getInstanceOf($rendererClass);
        if (! $renderer instanceof BackendPreviewRendererInterface) {
            throw new BackendPreviewException
            ('The given renderer class: ' . $rendererClass . ' has to implement the correct interface: '
             . BackendPreviewRendererInterface::class);
        }

        // Prepare the row
        $row             = $event->getRow();
        $row['settings'] = [];
        if (! empty($row['pi_flexform'])) {
            $row = array_merge($row,
                $this->getWithoutDi(FlexFormService::class)
                     ->convertFlexFormContentToArray($row['pi_flexform'])
            );
        }

        // Update the frontend language
        $languageUid = Arrays::getPath($row, ['sys_language_uid'], null);
        $this->getSingletonOf(EnvironmentSimulator::class)->runWithEnvironment(
            ['bootTsfe' => false, 'language' => $languageUid],
            function () use ($renderer, $event, $row) {
                try {
                    // Create the context and let the renderer run
                    $context = $this->getWithoutDi(
                        BackendPreviewRendererContext::class,
                        [$event]
                    );

                    if (method_exists($renderer, 'setContext')) {
                        $renderer->setContext($context);
                    }

                    $context->setHeader(empty($event->getHeader())
                        ? '<b>' . $this->findDefaultHeader($row) . '</b>'
                        : (string)$event->getHeader());
                    $context->setFooter(empty($event->getFooter())
                        ? $event->getUtils()->renderDefaultFooter()
                        : (string)$event->getFooter());
                    $context->setBody((string)$event->getBody());
                    $context->setLinkPreview(empty($event->getBody()));

                    $result = $renderer->renderBackendPreview($context);

                    if ($result instanceof ViewInterface) {
                        $result = $result->render();
                    } elseif ($result instanceof Response) {
                        $result = $result->getContent();
                    }

                    if (is_string($result)) {
                        $context->setBody($result);
                    }

                    // Add the description if required
                    $body = $context->getBody();
                    if ($context->showDescription()) {
                        $body = $this->renderDescription($event->getRow()) . $body;
                    }

                    // Check if we have to link the content
                    if (! empty($body) && $context->isLinkPreview()) {
                        $body = $event->getUtils()->wrapWithEditLink($body);
                    }

                    // Update event
                    $event->setBody($body);
                    $event->setFooter($context->getFooter());
                    $event->setHeader($context->getHeader());
                } catch (Throwable $e) {
                    $event->setBody(
                        $this->renderErrorMessage($this->stringifyThrowable($e))
                    );
                }
            }
        );
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
               . htmlentities($error) . '</div>';
    }

    protected function renderDescription(array $row): string
    {
        // Load the type descriptions from ts config
        if (! isset($this->typeDescriptions)) {
            $this->typeDescriptions = [];
            $items                  = $this->TypoContext()->Config()
                                           ->getTsConfigValue('mod.wizards.newContentElement.wizardItems');
            foreach ($items as $item) {
                if (! is_array($item['elements.'])) {
                    continue;
                }
                foreach ($item['elements.'] as $element) {
                    if (! is_string($element['description']) || ! is_array($element['tt_content_defValues.'])) {
                        continue;
                    }

                    $this->typeDescriptions[] = [$element['description'], $element['tt_content_defValues.']];
                }
            }

            // Sort the elements with more constraints to the top -> More specific matches first
            usort($this->typeDescriptions, static function (array $a, array $b) {
                return count($a[1]) < count($b[1]);
            });
        }

        foreach ($this->typeDescriptions as $description) {
            if (empty(array_diff_assoc($description[1], $row))) {
                // @todo translateBe
                return '<p><i>' . $this->Translator()->translate($description[0]) . '</i></p>';
            }
        }

        return '';
    }
}