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
 * Last modified: 2020.03.19 at 20:12
 */

namespace LaborDigital\Typo3BetterApi\BackendPreview;

use Exception;
use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\Event\Events\BackendListLabelFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\BackendPreviewRenderingEvent;
use LaborDigital\Typo3BetterApi\Simulation\EnvironmentSimulator;
use LaborDigital\Typo3BetterApi\Translation\TranslationService;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use Throwable;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Mvc\Response;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class BackendPreviewService implements SingletonInterface, BackendPreviewServiceInterface, LazyEventSubscriberInterface
{

    /**
     * The registered backend preview renderer classes and their respective constraints.
     *
     * @var array
     */
    protected $backendPreviewRenderers = [];

    /**
     * The registered list label renderer classes and their respective constraints.
     *
     * @var array
     */
    protected $backendListLabelRenderers = [];

    /**
     * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
     */
    protected $container;

    /**
     * @var \LaborDigital\Typo3BetterApi\Translation\TranslationService
     */
    protected $translationService;

    /**
     * @var \TYPO3\CMS\Core\Service\FlexFormService
     */
    protected $flexFormService;

    /**
     * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
     */
    protected $context;

    /**
     * BackendPreviewService constructor.
     *
     * @param   \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface  $container
     * @param   \LaborDigital\Typo3BetterApi\Translation\TranslationService    $translationService
     * @param   \TYPO3\CMS\Core\Service\FlexFormService                        $flexFormService
     * @param   \LaborDigital\Typo3BetterApi\TypoContext\TypoContext           $context
     */
    public function __construct(
        TypoContainerInterface $container,
        TranslationService $translationService,
        FlexFormService $flexFormService,
        TypoContext $context
    ) {
        $this->container          = $container;
        $this->translationService = $translationService;
        $this->flexFormService    = $flexFormService;
        $this->context            = $context;
    }

    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(BackendPreviewRenderingEvent::class, '__onBackendPreviewRendering');
        $subscription->subscribe(BackendListLabelFilterEvent::class, '__onBackendContentListLabelRendering');
    }

    /**
     * @inheritDoc
     */
    public function registerBackendPreviewRenderer(
        string $rendererClass,
        array $fieldConstraints,
        bool $override = false
    ): BackendPreviewService {
        $this->backendPreviewRenderers[md5(json_encode($fieldConstraints, JSON_THROW_ON_ERROR))] = [
            'constraints' => $fieldConstraints,
            'class'       => $rendererClass,
            'override'    => $override,
        ];

        return $this;
    }

    /**
     * Returns the list of all registered backend preview renderers
     *
     * @return array
     */
    public function getBackendPreviewRenderers(): array
    {
        return array_values($this->backendPreviewRenderers);
    }

    /**
     * @inheritDoc
     */
    public function registerBackendListLabelRenderer(
        $rendererClassOrColumns,
        array $fieldConstraints
    ): BackendPreviewService {
        if (! is_string($rendererClassOrColumns) && ! is_array($rendererClassOrColumns)) {
            throw new BackendPreviewException('The backend list label render can only be defined as class name or as array of field names');
        }
        $this->backendListLabelRenderers[md5(json_encode($fieldConstraints, JSON_THROW_ON_ERROR))] = [
            'constraints'    => $fieldConstraints,
            'classOrColumns' => $rendererClassOrColumns,
        ];

        return $this;
    }

    /**
     * Returns the list of all registered backend list label renderers
     *
     * @return array
     */
    public function getBackendListLabelRenderer(): array
    {
        return array_values($this->backendListLabelRenderers);
    }

    /**
     * Internal event handler that is called when the backend wants to draw a tt_content' preview in the page module
     *
     * @param   \LaborDigital\Typo3BetterApi\Event\Events\BackendPreviewRenderingEvent  $event
     */
    public function __onBackendPreviewRendering(BackendPreviewRenderingEvent $event): void
    {
        // Try to find a matching renderer
        $isRendered = $event->isRendered();
        foreach ($this->backendPreviewRenderers as $renderer) {
            // Check if this is an override
            if ($isRendered && ! $renderer['override']) {
                continue;
            }
            // Check if we match the constraints
            if (empty($renderer['constraints'])) {
                continue;
            }
            if (count(array_intersect_assoc($renderer['constraints'], $event->getRow()))
                !== count($renderer['constraints'])) {
                continue;
            }
            $this->callBackendPreviewRenderer($renderer['class'], $event);
            break;
        }
    }

    /**
     * Internal event handler that is called when the backend renders a tt_content' list label
     *
     * @param   \LaborDigital\Typo3BetterApi\Event\Events\BackendListLabelFilterEvent  $event
     */
    public function __onBackendContentListLabelRendering(BackendListLabelFilterEvent $event): void
    {
        // Try to find a matching renderer
        $title       = $this->findDefaultHeader($event->getRow());
        $foundLabel  = false;
        $fieldSlicer = static function ($v) {
            $v = strip_tags($v);
            if (strlen($v) > 100) {
                return trim(substr($v, 0, 100)) . '...';
            }

            return $v;
        };
        foreach ($this->backendListLabelRenderers as $renderer) {
            // Check if we match the constraints
            if (empty($renderer['constraints'])) {
                continue;
            }
            if (count(array_intersect_assoc($renderer['constraints'], $event->getRow()))
                !== count($renderer['constraints'])) {
                continue;
            }

            // Check if we got an array of columns
            if (is_array($renderer['classOrColumns'])) {
                $values = array_intersect_key($event->getRow(), array_fill_keys($renderer['classOrColumns'], true));
                $values = array_map($fieldSlicer, $values);
                $title  .= ' | ' . implode(' | ', $values);
            } else {
                // Try to call the renderer class
                $title .= ' | ' . $this->callBackendListLabelRenderer($renderer['classOrColumns'], $event);
            }
            $foundLabel = true;
            break;
        }

        // Check for fallback fields if we did not find a renderer for this label
        if (! $foundLabel) {
            foreach (['headline', 'title', 'header', 'bodytext', 'content', 'description', 'desc'] as $field) {
                if (empty($row[$field]) || ! is_string($row[$field]) || is_numeric($row[$field])) {
                    continue;
                }
                if (empty(trim(strip_tags($row[$field])))) {
                    continue;
                }
                $title .= ' | ' . $fieldSlicer($row[$field]);
                break;
            }
        }

        $event->setTitle($title);
    }

    /**
     * Main handler that receives the rendering class, executes the renderer and updates the event arguments.
     * It has also limited error handling capabilities that catch exceptions and render them as pretty message.
     *
     * @param   string                                                                  $rendererClass
     * @param   \LaborDigital\Typo3BetterApi\Event\Events\BackendPreviewRenderingEvent  $event
     *
     * @throws \LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewException
     */
    protected function callBackendPreviewRenderer(string $rendererClass, BackendPreviewRenderingEvent $event): void
    {
        // Check if the renderer class is valid
        if (! class_exists($rendererClass)) {
            throw new BackendPreviewException("The given renderer class: $rendererClass does not exist!");
        }
        $renderer = $this->container->get($rendererClass);
        if (! $renderer instanceof BackendPreviewRendererInterface) {
            throw new BackendPreviewException("The given renderer class: $rendererClass has to implement the correct interface: "
                                              . BackendPreviewRendererInterface::class);
        }

        // Prepare the row
        $row             = $event->getRow();
        $row['settings'] = [];
        if (! empty($row['pi_flexform'])) {
            $row = array_merge($row, $this->flexFormService->convertFlexFormContentToArray($row['pi_flexform']));
        }

        // Update the frontend language
        $languageUid = Arrays::getPath($row, ['sys_language_uid'], null);
        $this->container->get(EnvironmentSimulator::class)->runWithEnvironment(
            ['bootTsfe' => false, 'language' => $languageUid, 'includeHiddenPages'],
            function () use ($renderer, $event, $row) {
                try {
                    // Create the context and let the renderer run
                    $context = $this->container->get(
                        BackendPreviewRendererContext::class,
                        ['args' => [$event->getView(), $event, $row]]
                    );
                    if (method_exists($renderer, 'setContext')) {
                        $renderer->setContext($context);
                    }
                    $context->setBody($event->getContent());
                    $context->setHeader('<b>' . $this->findDefaultHeader($row) . '</b>');
                    $result = $renderer->renderBackendPreview($context);
                    if ($result instanceof ViewInterface) {
                        $result = $result->render();
                    } elseif ($result instanceof Response) {
                        $result = $result->getContent();
                    }
                    if (empty($result)) {
                        return;
                    }
                    if (is_string($result)) {
                        $context->setBody($result);
                    }

                    // Check if we have to link the content
                    $content = '<br>' . $context->getBody();
                    if ($context->isLinkPreview()) {
                        $content = $event->getView()->linkEditContent($content, $row);
                    }

                    // Update event
                    $event->setAsRendered();
                    $event->setContent($content);
                    $event->setHeader($context->getHeader());
                } catch (Exception $e) {
                    $event->setAsRendered();
                    $event->setContent($this->renderErrorMessage($e->getMessage() . ' (' . $e->getFile() . ':'
                                                                 . $e->getLine() . ')'));
                }
            }
        );
    }

    /**
     * Internal helper to call the backend list renderer class for the given row.
     * It will return the rendered label string that we should append to the title.
     *
     * @param   string                                                                 $rendererClass
     * @param   \LaborDigital\Typo3BetterApi\Event\Events\BackendListLabelFilterEvent  $event
     *
     * @return string
     */
    protected function callBackendListLabelRenderer(string $rendererClass, BackendListLabelFilterEvent $event): string
    {
        try {
            // Check if the renderer class is valid
            if (! class_exists($rendererClass)) {
                throw new BackendPreviewException("The given renderer class: $rendererClass does not exist!");
            }
            $renderer = $this->container->get($rendererClass);
            if (! $renderer instanceof BackendListLabelRendererInterface) {
                throw new BackendPreviewException(
                    "The given renderer class: $rendererClass has to implement the correct interface: "
                    . BackendListLabelRendererInterface::class);
            }

            // Call the renderer
            return $renderer->renderBackendListLabel($event->getRow(), $event->getOptions());
        } catch (Throwable $e) {
            return '[ERROR]: ' . $e->getMessage();
        }
    }

    /**
     * Internal helper that is used to resolve the default header based on the given database row.
     * If no header was found an empty string is returned
     *
     * @param   array  $row
     *
     * @return string
     */
    protected function findDefaultHeader(array $row): string
    {
        // Find for plugin
        if ($row['CType'] === 'list') {
            $signature = $row['list_type'];
            foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $listTypeItem) {
                if ($listTypeItem[1] !== $signature) {
                    continue;
                }

                return $this->translationService->translateBe($listTypeItem[0]);
            }

            return '';
        }

        // Find for content element
        $signature = $row['CType'];
        foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $listTypeItem) {
            if ($listTypeItem[1] !== $signature) {
                continue;
            }

            return $this->translationService->translateBe($listTypeItem[0]);
        }

        return '';
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
}
