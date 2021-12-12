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

namespace LaborDigital\T3ba\ExtBase\Controller;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Event\ExtBase\ActionController\MethodNameFilterEvent;
use LaborDigital\T3ba\Event\ExtBase\ActionController\RequestFilterEvent;
use LaborDigital\T3ba\Event\ExtBase\ActionController\ResponseFilterEvent;
use LaborDigital\T3ba\Tool\ExtBase\ExtBaseNotFoundHandler;
use LaborDigital\T3ba\Tool\Link\Link;
use LaborDigital\T3ba\Tool\Link\LinkService;
use LaborDigital\T3ba\Tool\Rendering\FlashMessageRenderingService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException;
use TYPO3\CMS\Extbase\Service\ExtensionService;

abstract class BetterActionController extends ActionController
{
    use ContainerAwareTrait;
    
    /**
     * Implements new hooks, catches a weired TYPO3 exception if a dbal entry was not found
     * and provides additional data attribute, containing the raw content element data
     *
     * @see https://forum.typo3.org/index.php?t=msg&goto=740402&
     *
     * @param   \TYPO3\CMS\Extbase\Mvc\RequestInterface  $request
     *
     */
    public function processRequest(RequestInterface $request): ResponseInterface
    {
        // Load the data from the content object
        if (empty($this->data)) {
            $this->data = $this->configurationManager->getContentObject()->data;
        }
        
        // Update the messaging service
        $messagingService = $this->getService(FlashMessageRenderingService::class);
        $extensionService = $this->getService(ExtensionService::class);
        $messagingService->setDefaultQueueId(
            'extbase.flashmessages.' . $extensionService->getPluginNamespace(
                $request->getControllerExtensionName(), $request->getPluginName())
        );
        
        $eventBus = $this->cs()->eventBus;
        $eventBus->dispatch(new RequestFilterEvent($request, $this));
        
        try {
            $response = parent::processRequest($request);
        } catch (TargetNotFoundException) {
            // Catch dbal overkill exceptions
        } finally {
            $messagingService->setDefaultQueueId(FlashMessageRenderingService::DEFAULT_QUEUE);
        }
        
        $eventBus->dispatch(new ResponseFilterEvent($response, $request, $this));
        
        return $response;
    }
    
    /**
     * Creates a new link instance which is a better version of the typo3 extbase query builder.
     * You can use this method anywhere, no matter if you are in an extbase controller, the cli
     * or somewhere in a hook you can always create links. For that we forcefully instantiate
     * the typo3 frontend if required.
     *
     * NOTE: Contrary to the getLink() method on LinkService the links generated through this method
     * already are aware of the controller context.
     *
     * @param   string|null    $definition    Allows you to provide the key of a link definition, which was
     *                                        configured using the ConfigureLinksInterface. The definition will
     *                                        automatically be applied to the new link instance
     * @param   iterable|null  $args          If you have a definition specified, you can use this parameter to supply
     *                                        additional arguments to the created link instance directly
     * @param   iterable|null  $fragmentArgs  If you have a definition specified, you can use this parameter to supply
     *                                        arguments to your fragment of the created link instance directly
     *
     * @return \LaborDigital\T3ba\Tool\Link\Link
     * @see LinkService::getLink()
     */
    protected function getLink(?string $definition = null, ?iterable $args = [], ?iterable $fragmentArgs = []): Link
    {
        return $this->getService(LinkService::class)
                    ->getLink($definition, $args, $fragmentArgs)
                    ->withRequest($this->request);
    }
    
    /**
     * @inheritDoc
     */
    protected function resolveActionMethodName()
    {
        $this->cs()->eventBus->dispatch(($e = new MethodNameFilterEvent(
            parent::resolveActionMethodName(),
            $this->request,
            $this
        )));
        
        return $e->getActionMethodName();
    }
    
    /**
     * Allows you to handle not found errors easily inside your extbase plugin or content element.
     * By default the TYPO3 default 404 handling will be executed when the method is triggered.
     * However you have multiple options to configure the behaviour yourself
     *
     * @param   string|null  $message  An optional message to show when the error is handled.
     *                                 This parameter is not used when redirectToPid or redirectToLink
     *                                 are configured.
     * @param   array        $options  Options to configure the error handling.
     *                                 - redirectToPid int|string: If set to a pid or pid selector
     *                                 the user will be redirected to the the page automatically.
     *                                 - redirectToLink string: Retrieves a link set identifier to
     *                                 redirect the user to. This is NOT used for absolute uris use redirectToUri instead.
     *                                 - renderTemplate string: Allows you to render the given fluid template
     *                                 and return the rendered content
     *
     * @return mixed|string
     */
    protected function handleNotFound(?string $message = null, array $options = [])
    {
        return $this->getService(ExtBaseNotFoundHandler::class)
                    ->handle($message, $options, function (string $target) {
                        /** @noinspection PhpVoidFunctionResultUsedInspection */
                        return $this->redirectToUri($target);
                    });
    }
}
