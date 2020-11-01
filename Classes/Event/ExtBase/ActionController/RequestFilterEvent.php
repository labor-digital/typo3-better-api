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
 * Last modified: 2020.09.09 at 00:24
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Event\ExtBase\ActionController;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;

/**
 * Class RequestFilterEvent
 *
 * Emitted when a "Better action controller" ext base action controller is executed.
 * Can be used to filter the response and request.
 *
 * Called twice. Once before and once after the request was processed.
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class RequestFilterEvent
{

    /**
     * The ext base request object to handle
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
     * True if the event is emitted before and false if emitted after the processRequest() method of the controller
     *
     * @var bool
     */
    protected $beforeProcessing;

    /**
     * RequestFilterEvent constructor.
     *
     * @param   \TYPO3\CMS\Extbase\Mvc\RequestInterface             $request
     * @param   \TYPO3\CMS\Extbase\Mvc\ResponseInterface            $response
     * @param   \TYPO3\CMS\Extbase\Mvc\Controller\ActionController  $controller
     * @param   bool                                                $beforeProcessing
     */
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ActionController $controller,
        bool $beforeProcessing
    ) {
        $this->request          = $request;
        $this->response         = $response;
        $this->controller       = $controller;
        $this->beforeProcessing = $beforeProcessing;
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

    /**
     * Returns true if the event is emitted before and false if emitted after the processRequest() method of the
     * controller
     *
     * @return bool
     */
    public function isBeforeProcessing(): bool
    {
        return $this->beforeProcessing;
    }
}
