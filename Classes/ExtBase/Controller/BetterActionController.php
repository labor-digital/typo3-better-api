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
 * Last modified: 2020.08.23 at 23:23
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\ExtBase\Controller;

use LaborDigital\T3BA\Core\DependencyInjection\CommonDependencyTrait;
use LaborDigital\T3BA\Event\ActionControllerMethodNameFilterEvent;
use LaborDigital\T3BA\Event\ActionControllerRequestFilterEvent;
use LaborDigital\T3BA\Tool\Link\LinkService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException;

class BetterActionController extends ActionController
{
    use CommonDependencyTrait;

    /**
     * The list of the raw content object data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Implements new hooks, catches a weired TYPO3 exception if a dbal entry was not found
     * and provides additional data attribute, containing the raw content element data
     *
     * @see https://forum.typo3.org/index.php?t=msg&goto=740402&
     *
     * @param   \TYPO3\CMS\Extbase\Mvc\RequestInterface   $request
     * @param   \TYPO3\CMS\Extbase\Mvc\ResponseInterface  $response
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function processRequest(RequestInterface $request, ResponseInterface $response)
    {
        // Load the data from the content object
        if (empty($this->data)) {
            $this->data = $this->configurationManager->getContentObject()->data;
        }

        // Inject the this controller's request into the links object
        $this->setLocalSingleton(
            LinkService::class, $this->Links()->makeControllerClone($request)
        );

        // Allow filtering
        $this->EventBus()->dispatch(new ActionControllerRequestFilterEvent($request, $response, $this, true));

        // Do the default stuff
        try {
            parent::processRequest($request, $response);
        } catch (TargetNotFoundException $e) {
            // Catch dbal overkill exceptions
        }

        // Allow filtering
        $this->EventBus()->dispatch(new ActionControllerRequestFilterEvent($request, $response, $this, false));
    }

    /**
     * @inheritDoc
     */
    protected function resolveActionMethodName()
    {
        $this->EventBus()->dispatch(($e = new ActionControllerMethodNameFilterEvent(
            parent::resolveActionMethodName(),
            $this->request,
            $this->response,
            $this
        )));

        return $e->getActionMethodName();
    }
}
