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

namespace LaborDigital\T3ba\ExtBase\Controller;

use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Tool\BackendPreview\BackendPreviewException;
use LaborDigital\T3ba\Tool\BackendPreview\BackendPreviewRendererContext;
use LaborDigital\T3ba\Tool\OddsAndEnds\ReflectionUtil;
use LaborDigital\T3ba\Tool\Rendering\TemplateRenderingService;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use Neunerlei\Options\Options;
use RuntimeException;
use Throwable;
use TypeError;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Event\Mvc\BeforeActionCallEvent;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Dispatcher;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Trait ExtBaseBackendPreviewRendererTrait
 *
 * This trait helps you to render the backend preview in extbase controllers
 *
 * @package LaborDigital\T3ba\ExtBase\Controller
 */
trait ContentControllerBackendPreviewTrait
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
    protected $previewRendererContext;
    
    /**
     * Injects the context when the renderer is instantiated
     *
     * @param   BackendPreviewRendererContext  $context
     */
    public function setBackendPreviewRendererContext(BackendPreviewRendererContext $context): void
    {
        $this->previewRendererContext = $context;
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
    protected function getFluidView(string $templateName = 'BackendPreview'): StandaloneView
    {
        ControllerUtil::requireActionController($this);
        /** @var ActionController $this */
        
        $config = $this->configurationManager
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $typoContext = TypoContext::getInstance();
        $typoScript = $typoContext->di()->cs()->ts;
        $viewConfig = $typoScript->removeDots($config['view'] ?? []);
        
        // Make and prepare the view instance
        return $typoContext->di()
                           ->getService(TemplateRenderingService::class)
                           ->getFluidView($templateName, $viewConfig);
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
    protected function simulateRequest(string $actionName, array $options = []): ResponseInterface
    {
        ControllerUtil::requireActionController($this);
        
        static::$transfer['context'] = $this->previewRendererContext;
        /** @var ActionController $this */
        
        // Prepare the options
        static::$transfer['options'] = Options::make($options, [
            'additionalArgs' => [
                'type' => 'array',
                'default' => [],
            ],
            'templateName' => [
                'type' => 'string',
                'default' => 'BackendPreview',
            ],
        ]);
        
        // Create a new request
        $objectManager = $this->objectManager;
        /** @noinspection PhpParamsInspection */
        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $objectManager->get(RequestInterface::class, static::class);
        
        $request->setControllerObjectName(static::class);
        $request->setControllerActionName($actionName);
        $pluginName = $this->getService(ExtensionService::class)->getPluginNameByAction(
            $request->getControllerExtensionName(),
            $request->getControllerName(),
            $actionName
        );
        $request->setPluginName($pluginName);
        $request->setArguments(static::$transfer['options']['additionalArgs']);
        $request->setFormat('html');
        
        // Create a response and dispatcher
        $response = $objectManager->get(ResponseInterface::class);
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
     *                                    Instead of an "actionName" you could also provide a boolean false,
     *                                    which will disable the simulation request for a specific variant.
     * @param   array  $options           The same options you can pass for {@link simulateRequest()}
     *
     * @return \TYPO3\CMS\Extbase\Mvc\ResponseInterface
     * @throws \LaborDigital\T3ba\Tool\BackendPreview\BackendPreviewException
     */
    protected function simulateVariantRequest(array $variantActionMap, array $options = []): ResponseInterface
    {
        ControllerUtil::requireActionController($this);
        
        $variant = $this->previewRendererContext->getVariant();
        $action = $variantActionMap[$variant] ?? $variantActionMap['default'] ?? null;
        
        if ($action === null) {
            if ($variant === null) {
                throw new BackendPreviewException('Could not resolve a action for the "default" variant.');
            }
            throw new BackendPreviewException('Could not resolve a action for variant: ' . $variant);
        }
        
        if ($action === false) {
            /** @var ActionController $this */
            $response = $this->objectManager->get(ResponseInterface::class);
            $response->setContent('');
            
            return $response;
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
     * We use this method to override the basic controller properties.
     * Also provides the required environment properties to create a "mostly" real ext-base controller experience.
     *
     * @internal
     */
    protected function registerEnvironmentSetup(): void
    {
        TypoEventBus::getInstance()->addListener(BeforeActionCallEvent::class, static function () {
            if (empty(static::$transfer)) {
                return;
            }
            
            // We have to use this hack to get the instance of the controller,
            // because someone was clever enough to not include that instance into the event -.-
            $controller = ReflectionUtil::getClosestFromStack(static::class, 6);
            if (! $controller instanceof self) {
                throw new RuntimeException('Failed to locate the target controller for the simulated request!');
            }
            
            $controller->previewRendererContext = static::$transfer['context'];
            
            try {
                $controller->view = $controller->getFluidView(static::$transfer['options']['templateName']);
            } catch (Throwable $e) {
                // Silence
            }
        }, ['once']);
    }
}
