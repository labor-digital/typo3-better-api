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

namespace LaborDigital\T3ba\Event\ExtBase\ActionController;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * Class ResponseFilterEvent
 *
 * Emitted when a "Better action controller" ext base action controller is invoked, after the action was executed
 *
 * @package LaborDigital\T3ba\Event\ExtBase\ActionController
 */
class ResponseFilterEvent
{
    
    /**
     * The response object returned by the action method
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected ResponseInterface $response;
    
    /**
     * The ext base request object to handle
     *
     * @var \TYPO3\CMS\Extbase\Mvc\RequestInterface
     */
    protected RequestInterface $request;
    
    /**
     * The controller to handle the request
     *
     * @var \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
     */
    protected ActionController $controller;
    
    /**
     * @param   \Psr\Http\Message\ResponseInterface                 $response
     * @param   \TYPO3\CMS\Extbase\Mvc\RequestInterface             $request
     * @param   \TYPO3\CMS\Extbase\Mvc\Controller\ActionController  $controller
     */
    public function __construct(
        ResponseInterface $response,
        RequestInterface $request,
        ActionController $controller
    )
    {
        $this->response = $response;
        $this->request = $request;
        $this->controller = $controller;
    }
    
    /**
     * Returns the response object returned by the action method
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
    
    /**
     * Allows you to override the response object returned by the action method
     *
     * @param   \Psr\Http\Message\ResponseInterface  $response
     *
     * @return ResponseFilterEvent
     */
    public function setResponse(ResponseInterface $response): ResponseFilterEvent
    {
        $this->response = $response;
        
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
     * Returns the controller to handle the request
     *
     * @return \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
     */
    public function getController(): ActionController
    {
        return $this->controller;
    }
}
