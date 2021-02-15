<?php
declare(strict_types=1);
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
 * Last modified: 2020.08.23 at 23:23
 */

namespace LaborDigital\T3BA\ExtBase\Controller;

use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Tool\BackendPreview\BackendPreviewException;
use LaborDigital\T3BA\Tool\BackendPreview\BackendPreviewRendererContext;
use LaborDigital\T3BA\Tool\Rendering\TemplateRenderingService;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Options\Options;
use RuntimeException;
use TypeError;
use TYPO3\CMS\Extbase\Event\Mvc\BeforeActionCallEvent;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Dispatcher;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;

/**
 * Trait ExtBaseBackendPreviewRendererTrait
 *
 * This trait helps you to render the backend preview in extbase controllers
 *
 * @package LaborDigital\Typo3BetterApi\BackendPreview
 */
trait ExtBaseBackendPreviewRendererTrait
{
    /**
     * Internal helper to move some stuff around
     *
     * @var array
     */
    protected static $transfer = [];

    /**
     * The context instance for this renderer
     *
     * @var BackendPreviewRendererContext
     */
    protected $context;

    /**
     * Holds the data if the backend action is executed
     *
     * @var array|null
     */
    protected $data = [];

    /**
     * Injects the context when the renderer is instantiated
     *
     * @param   BackendPreviewRendererContext  $context
     */
    public function setContext(BackendPreviewRendererContext $context): void
    {
        $this->context = $context;
    }

    /**
     * Returns a prepared fluid view you can use to render your backend preview with.
     * There are two variables already defined: "data" contains the raw db row and "settings" contains everything your
     * flex form defined as "settings". The flex form was already unpacked and merged into the row
     *
     * @param   string  $templateName
     *
     * @return \TYPO3\CMS\Fluid\View\StandaloneView
     */
    public function getFluidView(string $templateName = 'BackendPreview'): StandaloneView
    {
        // Load the view configuration from typoScript
        $row        = $this->context->getRow();
        $signature  = $row['CType'] === 'list' ? $row['list_type'] : $row['CType'];
        $configType = $row['CType'] === 'list' ? 'plugin' : 'contentElement';
        if (strpos($signature, 'tx_') !== 0) {
            $signature = 'tx_' . $signature;
        }
        $typoContext = TypoContext::getInstance();
        $typoScript  = $typoContext->di()->cs()->ts;
        $viewConfig  = $typoScript->get([$configType, $signature, 'view'], ['default' => []]);
        $viewConfig  = $typoScript->removeDots($viewConfig);

        // Make and prepare the view instance
        $view = $typoContext->di()
                            ->getService(TemplateRenderingService::class)
                            ->getFluidView($templateName, $viewConfig);
        $view->assign('settings', $row['settings']);
        $view->assign('data', $row);

        // Done
        return $view;
    }

    /**
     * This helper is used execute an extbase request in the backend.
     * The given action will be executed on the current controller class.
     * The resulting response will be returned
     *
     * @param   string  $actionName  The action method to execute. (Without "...Action")
     * @param   array   $options     Additional options for this action handler
     *                               - additionalArgs array: Additional arguments that should be passed to the
     *                               controller action method.
     *                               - templateName string (BackendPreview): Can be used to change the default template
     *                               name for the backend preview.
     *
     * @return \TYPO3\CMS\Extbase\Mvc\ResponseInterface
     */
    public function simulateRequest(string $actionName, array $options = []): ResponseInterface
    {
        $this->validateThatBePreviewTraitIsCalledInActionController();
        static::$transfer['context']  = $this->context;
        static::$transfer['settings'] = isset($this->settings) && is_array($this->settings) ? $this->settings : [];
        static::$transfer['data']     = isset($this->data) && is_array($this->data) ? $this->data : [];

        // Prepare the options
        static::$transfer['options'] = Options::make($options, [
            'additionalArgs' => [
                'type'    => 'array',
                'default' => [],
            ],
            'templateName'   => [
                'type'    => 'string',
                'default' => 'BackendPreview',
            ],
        ]);

        /** @var ActionController $this */
        $objectManager = $this->objectManager;
        $row           = $this->context->getRow();
        $listType      = $row['list_type'];
        $typoContext   = TypoContext::getInstance();
        $typoScript    = $typoContext->di()->cs()->ts;
        $config        = $typoScript->get(['plugin', 'tx_' . $listType], ['default' => []]);

        // Prepare the config manager
        /** @var ActionController $this */
        $configManager = $this->configurationManager;
        $configManager->setConfiguration($config);

        // Create a new request
        $controllerClass = get_called_class();
        $request         = $objectManager->get(RequestInterface::class, $controllerClass);
        $request->setPluginName($this->context->getRow()['list_type']);
        $request->setControllerObjectName($controllerClass);
        $request->setControllerActionName($actionName);
        $request->setArguments(static::$transfer['options']['additionalArgs']);
        $request->setFormat('html');

        // Create a response and dispatcher
        $response   = $objectManager->get(ResponseInterface::class);
        $dispatcher = $objectManager->get(Dispatcher::class);
        $this->registerEnvironmentSetup();
        $dispatcher->dispatch($request, $response);

        // Remove transfer
        static::$transfer = null;

        // Done
        return $response;
    }

    /**
     * Similar to simulateRequest() but allows you to provide a map of plugin variants and their action methods,
     * instead of a single, static action name.
     *
     * @param   array  $variantActionMap  An associative array of "variantName" => "actionName" that defines
     *                                    which action should be triggered if a certain variant of a plugin is used.
     *                                    "default" is used as a protected key that points to the default plugin
     *                                    configuration.
     * @param   array  $options           The same options you can pass for {@link simulateRequest()}
     *
     * @return \TYPO3\CMS\Extbase\Mvc\ResponseInterface
     * @throws \LaborDigital\T3BA\Tool\BackendPreview\BackendPreviewException
     */
    protected function simulateVariantRequest(array $variantActionMap, array $options = []): ResponseInterface
    {
        $variant = $this->context->getPluginVariant();
        $action  = $variantActionMap[$variant] ?? $variantActionMap['default'] ?? null;

        if ($action === null) {
            if ($variant === null) {
                throw new BackendPreviewException('Could not resolve a action for the "default" variant.');
            }
            throw new BackendPreviewException('Could not resolve a action for variant: ' . $variant);
        }

        if (! is_string($action)) {
            throw new TypeError(
                'Invalid $variantActionMap configuration! Only a single action can be mapped for a variant request! ' .
                'The value for variant: ' . $variant . ' was resolved to a value of type: ' . gettype($action));
        }

        if ($variant !== null && empty($options['templateName'])) {
            $options['templateName'] = 'BackendPreview' . ucfirst($variant);
        }

        return $this->simulateRequest($action, $options);
    }

    /**
     * Internal helper to check if all our required properties exist
     *
     * @throws \LaborDigital\T3BA\Tool\BackendPreview\BackendPreviewException
     */
    protected function validateThatBePreviewTraitIsCalledInActionController(): void
    {
        if (! $this instanceof ActionController) {
            throw new BackendPreviewException('To use this trait you have to call it in an ActionController action!');
        }
    }

    /**
     * We use this method to override the basic controller properties.
     * Also provides the required environment properties to create a "mostly" real ext-base controller experience.
     */
    protected function registerEnvironmentSetup(): void
    {
        TypoEventBus::getInstance()->addListener(BeforeActionCallEvent::class, static function () {
            if (empty(static::$transfer)) {
                return;
            }

            $registered = false;

            // We have to use this hack to get the instance of the controller,
            // because someone was clever enough to not include that instance into the event -.-
            foreach (debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 6) as $call) {
                if (isset($call['object']) && $call['object'] instanceof self) {
                    /** @var self $controller */
                    $controller          = $call['object'];
                    $controller->context = static::$transfer['context'];

                    if (property_exists($controller, 'data')) {
                        $controller->data = static::$transfer['data'];
                    }

                    $controller->settings = is_array($controller->settings)
                        ? Arrays::merge($controller->settings, static::$transfer['settings'])
                        : $controller->data['settings'];

                    try {
                        $controller->view = $controller->getFluidView(static::$transfer['options']['templateName']);
                    } catch (InvalidTemplateResourceException $e) {
                        // Silence
                    }

                    $registered = true;
                    break;
                }
            }

            if (! $registered) {
                throw new RuntimeException('Failed to locate the target controller for the simulated request!');
            }
        }, ['once']);
    }
}
