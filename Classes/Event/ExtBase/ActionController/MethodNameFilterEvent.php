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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Event\ExtBase\ActionController;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;

/**
 * Class MethodNameFilterEvent
 *
 * Emitted when a "Better action controller" extbase action controller is executed.
 * Can be used to filter the action method name before it is invoked.
 *
 * @package LaborDigital\T3BA\Event\ExtBase\ActionController
 */
class MethodNameFilterEvent
{
    /**
     * The name of the action to filter
     *
     * @var string
     */
    protected $actionName;
    
    /**
     * The extbase request object to handle
     *
     * @var \TYPO3\CMS\Extbase\Mvc\RequestInterface
     */
    protected $request;
    
    /**
     * The ext base response object to dump the contents into
     *
     * @var \TYPO3\CMS\Extbase\Mvc\ResponseInterface
     */
    protected $response;
    
    /**
     * The controller to handle the request
     *
     * @var \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
     */
    protected $controller;
    
    /**
     * MethodNameFilterEvent constructor.
     *
     * @param   string                                              $actionName
     * @param   \TYPO3\CMS\Extbase\Mvc\RequestInterface             $request
     * @param   \TYPO3\CMS\Extbase\Mvc\ResponseInterface            $response
     * @param   \TYPO3\CMS\Extbase\Mvc\Controller\ActionController  $controller
     */
    public function __construct(
        string $actionName,
        RequestInterface $request,
        ResponseInterface $response,
        ActionController $controller
    )
    {
        $this->actionName = $actionName;
        $this->request = $request;
        $this->response = $response;
        $this->controller = $controller;
    }
    
    /**
     * Returns the name of the action to filter
     *
     * @return string
     */
    public function getActionMethodName(): string
    {
        return $this->actionName;
    }
    
    /**
     * Updates the name of the action to invoke
     *
     * @param   string  $actionName
     *
     * @return MethodNameFilterEvent
     */
    public function setActionMethodName(string $actionName): MethodNameFilterEvent
    {
        $this->actionName = $actionName;
        
        return $this;
    }
    
    /**
     * Returns the extbase request object to handle
     *
     * @return \TYPO3\CMS\Extbase\Mvc\RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
    
    /**
     * Returns the ext base response object to dump the contents into
     *
     * @return \TYPO3\CMS\Extbase\Mvc\ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
    
    /**
     * Returns the controller to handle the request
     *
     * @return \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
     */
    public function getController(): ActionController
    {
        return $this->controller;
    }
}
