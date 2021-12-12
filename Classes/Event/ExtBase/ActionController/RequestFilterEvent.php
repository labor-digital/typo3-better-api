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

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * Class RequestFilterEvent
 *
 * Emitted when a "Better action controller" ext base action controller is invoked, before the action is executed
 *
 * @package LaborDigital\T3ba\Event\ExtBase\ActionController
 */
class RequestFilterEvent
{
    
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
     * @param   \TYPO3\CMS\Extbase\Mvc\RequestInterface             $request
     * @param   \TYPO3\CMS\Extbase\Mvc\Controller\ActionController  $controller
     */
    public function __construct(
        RequestInterface $request,
        ActionController $controller
    )
    {
        $this->request = $request;
        $this->controller = $controller;
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
