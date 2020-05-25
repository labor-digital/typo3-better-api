<?php
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
 * Last modified: 2020.03.19 at 02:50
 */

namespace LaborDigital\Typo3BetterApi\BackendPreview;

use Neunerlei\Arrays\Arrays;
use Neunerlei\Options\Options;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Dispatcher;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Trait ExtBaseBackendPreviewRendererTrait
 *
 * This trait helps you to render the backend preview in extbase controllers
 *
 * @package LaborDigital\Typo3BetterApi\BackendPreview
 */
trait ExtBaseBackendPreviewRendererTrait
{
    protected static $transfer = [];
    
    /**
     * The context instance for this renderer
     * @var \LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewRendererContext
     */
    protected $context;
    
    /**
     * Holds the data if the backend action is executed
     * @var array|null
     */
    protected $data = [];
    
    /**
     * Injects the context when the renderer is instantiated
     *
     * @param \LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewRendererContext $context
     */
    public function setContext(BackendPreviewRendererContext $context)
    {
        $this->context = $context;
    }
    
    /**
     * Returns a prepared fluid view you can use to render your backend preview with.
     * There are two variables already defined: "data" contains the raw db row and "settings" contains everything your
     * flex form defined as "settings". The flex form was already unpacked and merged into the row
     *
     * @param string $templateName
     *
     * @return \TYPO3\CMS\Fluid\View\StandaloneView
     */
    public function getFluidView(string $templateName = 'BackendPreview'): StandaloneView
    {
        
        // Load the view configuration from typoScript
        $row = $this->context->getRow();
        $signature = $row['CType'] === 'list' ? $row['list_type'] : $row['CType'];
        $configType = $row['CType'] === 'list' ? 'plugin' : 'contentElement';
        if (substr($signature, 0, 3) !== 'tx_') {
            $signature = 'tx_' . $signature;
        }
        $viewConfig = $this->context->TypoScript->get([$configType, $signature, 'view'], ['default' => []]);
        $viewConfig = $this->context->TypoScript->removeDots($viewConfig);
        
        // Make and prepare the view instance
        $view = $this->context->TemplateRendering->getFluidView($templateName, $viewConfig);
        $view->assign('settings', $row['settings']);
        $view->assign('data', $row);
        
        // Done
        return $view;
    }
    
    /**
     * We use this method to override the basic controller properties.
     * Also provides the required environment properties to create a "mostly" real ext-base controller experience.
     *
     * @param array $preparedArguments
     *
     * @return \TYPO3\CMS\Extbase\Mvc\View\ViewInterface|\TYPO3\CMS\Fluid\View\StandaloneView
     */
    protected function emitBeforeCallActionMethodSignal(array $preparedArguments)
    {
        if (!empty(static::$transfer)) {
            $this->context = static::$transfer['context'];
            $this->data = $this->context->getRow();
            $this->settings = is_array($this->settings) ?
                Arrays::merge($this->settings, $this->data['settings']) : $this->data['settings'];
            try {
                $this->view = $this->getFluidView(static::$transfer['options']['templateName']);
            } catch (InvalidTemplateResourceException $e) {
                // Silence
            }
        }
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::emitBeforeCallActionMethodSignal($preparedArguments);
    }
    
    /**
     * This helper is used execute an extbase request in the backend.
     * The given action will be executed on the current controller class.
     * The resulting response will be returned
     *
     * @param string $actionName The action method to execute. (Without "...Action")
     * @param array  $options    Additional options for this action handler
     *                           - additionalArgs array: Additional arguments that should be passed to the controller
     *                           action method.
     *                           - templateName string (BackendPreview): Can be used to change the default template
     *                           name for the backend preview.
     *
     * @return \TYPO3\CMS\Extbase\Mvc\ResponseInterface
     */
    public function callBackendAction(string $actionName, array $options = []): ResponseInterface
    {
        $this->validateThatBePreviewTraitIsCalledInActionController();
        static::$transfer['context'] = $this->context;
        
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
        $row = $this->context->getRow();
        $listType = $row['list_type'];
        $config = $this->context->TypoScript->get(['plugin', 'tx_' . $listType], ['default' => []]);
        
        // Prepare the config manager
        /** @var ActionController $this */
        $configManager = $this->configurationManager;
        $configManager->setConfiguration($config);
        
        // Create a new request
        $request = $objectManager->get(RequestInterface::class);
        $request->setPluginName($this->context->getRow()['list_type']);
        $request->setControllerObjectName(get_called_class());
        $request->setControllerActionName($actionName);
        $request->setArguments(static::$transfer['options']['additionalArgs']);
        $request->setFormat('html');
        
        // Create a response and dispatcher
        $response = $objectManager->get(ResponseInterface::class);
        $dispatcher = $objectManager->get(Dispatcher::class);
        $dispatcher->dispatch($request, $response);
        
        // Remove transfer
        static::$transfer = null;
        
        // Done
        return $response;
    }
    
    /**
     * Internal helper to check if all our required properties exist
     *
     * @throws \LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewException
     */
    protected function validateThatBePreviewTraitIsCalledInActionController()
    {
        if (!$this instanceof ActionController) {
            throw new BackendPreviewException('To use this trait you have to call it in an ActionController action!');
        }
    }
}
