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
 * Last modified: 2020.03.19 at 01:35
 */

namespace LaborDigital\Typo3BetterApi\Rendering;

use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\SingletonInterface;

class FlashMessageRenderingService implements SingletonInterface
{
    
    /**
     * @var \TYPO3\CMS\Core\Messaging\FlashMessageService
     */
    protected $flashMessageService;
    
    /**
     * @var \TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver
     */
    protected $rendererResolver;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
     */
    protected $container;
    
    
    /**
     * FlashMessageRenderingService constructor.
     *
     * @param   \TYPO3\CMS\Core\Messaging\FlashMessageService                  $flashMessageService
     * @param   \TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver         $rendererResolver
     * @param   \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface  $container
     */
    public function __construct(
        FlashMessageService $flashMessageService,
        FlashMessageRendererResolver $rendererResolver,
        TypoContainerInterface $container
    ) {
        $this->flashMessageService = $flashMessageService;
        $this->rendererResolver    = $rendererResolver;
        $this->container           = $container;
    }
    
    /**
     * Adds a new flash message of type "NOTICE" to the stack
     *
     * @param   string  $message  The message to be shown
     * @param   string  $header   The header of the message
     * @param   array   $options  Additional options when creating your message
     *                            - queueId string (core.template.flashMessages) the stack identifier to
     *                            add this message to.
     *                            - storeInSession bool (FALSE) If set to true the message will be stored in the session
     *                            instead of the local context. If the message is stored in the session it survives page
     *                            changes and redirects.
     *
     * @return \LaborDigital\Typo3BetterApi\Rendering\FlashMessageRenderingService
     */
    public function addNotice(string $message, string $header = '', array $options = [])
    {
        return $this->addMessageInternal(FlashMessage::NOTICE, $message, $header, $options);
    }
    
    /**
     * Adds a new flash message of type "WARNING" to the stack
     *
     * @param   string  $message  The message to be shown
     * @param   string  $header   The header of the message
     * @param   array   $options  Additional options when creating your message
     *                            - queueId string (core.template.flashMessages) the stack identifier to
     *                            add this message to.
     *                            - storeInSession bool (FALSE) If set to true the message will be stored in the session
     *                            instead of the local context. If the message is stored in the session it survives page
     *                            changes and redirects.
     *
     * @return \LaborDigital\Typo3BetterApi\Rendering\FlashMessageRenderingService
     */
    public function addWarning(string $message, string $header = '', array $options = [])
    {
        return $this->addMessageInternal(FlashMessage::WARNING, $message, $header, $options);
    }
    
    /**
     * Adds a new flash message of type "OK" to the stack
     *
     * @param   string  $message  The message to be shown
     * @param   string  $header   The header of the message
     * @param   array   $options  Additional options when creating your message
     *                            - queueId string (core.template.flashMessages) the stack identifier to
     *                            add this message to.
     *                            - storeInSession bool (FALSE) If set to true the message will be stored in the session
     *                            instead of the local context. If the message is stored in the session it survives page
     *                            changes and redirects.
     *
     * @return \LaborDigital\Typo3BetterApi\Rendering\FlashMessageRenderingService
     */
    public function addOk(string $message, string $header = '', array $options = [])
    {
        return $this->addMessageInternal(FlashMessage::OK, $message, $header, $options);
    }
    
    /**
     * Adds a new flash message of type "INFO" to the stack
     *
     * @param   string  $message  The message to be shown
     * @param   string  $header   The header of the message
     * @param   array   $options  Additional options when creating your message
     *                            - queueId string (core.template.flashMessages) the stack identifier to
     *                            add this message to.
     *                            - storeInSession bool (FALSE) If set to true the message will be stored in the session
     *                            instead of the local context. If the message is stored in the session it survives page
     *                            changes and redirects.
     *
     * @return \LaborDigital\Typo3BetterApi\Rendering\FlashMessageRenderingService
     */
    public function addInfo(string $message, string $header = '', array $options = [])
    {
        return $this->addMessageInternal(FlashMessage::INFO, $message, $header, $options);
    }
    
    /**
     * Adds a new flash message of type "ERROR" to the stack
     *
     * @param   string  $message  The message to be shown
     * @param   string  $header   The header of the message
     * @param   array   $options  Additional options when creating your message
     *                            - queueId string (core.template.flashMessages) the stack identifier to
     *                            add this message to.
     *                            - storeInSession bool (FALSE) If set to true the message will be stored in the session
     *                            instead of the local context. If the message is stored in the session it survives page
     *                            changes and redirects.
     *
     * @return \LaborDigital\Typo3BetterApi\Rendering\FlashMessageRenderingService
     */
    public function addError(string $message, string $header = '', array $options = [])
    {
        return $this->addMessageInternal(FlashMessage::ERROR, $message, $header, $options);
    }
    
    /**
     * Can be used to render the stack of flash messages to a variable.
     * NOTE: This is supported from Typo3 v8 and up!
     *
     * @param   string  $queueId  The query id of messages to render to the variable
     *
     * @return string
     */
    public function renderMessages(string $queueId = 'core.template.flashMessages'): string
    {
        // Get the stack
        $stack = $this->flashMessageService->getMessageQueueByIdentifier($queueId);
        
        return $this->rendererResolver->resolve()->render($stack->getAllMessagesAndFlush());
    }
    
    /**
     * Internal helper which is used to validate the given options and to create the message instance on a certain
     * stack.
     *
     * @param   int     $type
     * @param   string  $message
     * @param   string  $header
     * @param   array   $options
     *
     * @return $this
     */
    protected function addMessageInternal(int $type, string $message, string $header, array $options)
    {
        // Prepare the options
        $options = Options::make($options, [
            'storeInSession' => [
                'type'    => 'bool',
                'default' => false,
            ],
            'queueId'        => [
                'type'    => 'string',
                'default' => 'core.template.flashMessages',
            ],
        ]);
        
        // Create the new message
        $message = $this->container->get(
            FlashMessage::class,
            [
                'args' => [
                    $message,
                    $header,
                    $type,
                    $options['storeInSession'],
                ],
                'gu',
            ]
        );
        
        // Get the stack
        $stack = $this->flashMessageService->getMessageQueueByIdentifier($options['queueId']);
        $stack->addMessage($message);
        
        // Done
        return $this;
    }
}
