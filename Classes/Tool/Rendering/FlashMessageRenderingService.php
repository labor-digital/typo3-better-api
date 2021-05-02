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


namespace LaborDigital\T3ba\Tool\Rendering;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\SingletonInterface;

class FlashMessageRenderingService implements SingletonInterface
{
    use ContainerAwareTrait;
    
    public const DEFAULT_QUEUE = 'core.template.flashMessages';
    
    /**
     * Holds the default queue id that is used when a message is added without a specific id
     *
     * @var string
     */
    protected $defaultQueueId = self::DEFAULT_QUEUE;
    
    /**
     * @var \TYPO3\CMS\Core\Messaging\FlashMessageService
     */
    protected $flashMessageService;
    
    /**
     * @var \TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver
     */
    protected $rendererResolver;
    
    /**
     * FlashMessageRenderingService constructor.
     *
     * @param   \TYPO3\CMS\Core\Messaging\FlashMessageService           $flashMessageService
     * @param   \TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver  $rendererResolver
     */
    public function __construct(
        FlashMessageService $flashMessageService,
        FlashMessageRendererResolver $rendererResolver
    )
    {
        $this->flashMessageService = $flashMessageService;
        $this->rendererResolver = $rendererResolver;
    }
    
    /**
     * Returns the queue id which is used if none was specifically defined
     *
     * @return string
     */
    public function getDefaultQueueId(): string
    {
        return $this->defaultQueueId;
    }
    
    /**
     * Allows you to override the default queue id
     *
     * @param   string  $id
     *
     * @return $this
     */
    public function setDefaultQueueId(string $id): self
    {
        $this->defaultQueueId = $id;
        
        return $this;
    }
    
    /**
     * Resets the default queue id back to the value of DEFAULT_QUEUE
     *
     * @return $this
     */
    public function resetDefaultQueueId(): self
    {
        $this->defaultQueueId = static::DEFAULT_QUEUE;
        
        return $this;
    }
    
    /**
     * Adds a new flash message of type "NOTICE" to the stack
     *
     * @param   string       $message  The message to be shown
     * @param   string|null  $header   The header of the message
     * @param   array        $options  Additional options when creating your message
     *                                 - queueId string (core.template.flashMessages) the stack identifier to
     *                                 add this message to.
     *                                 - storeInSession bool (FALSE) If set to true the message will be stored in the
     *                                 session instead of the local context. If the message is stored in the session it
     *                                 survives page changes and redirects.
     *
     * @return $this
     */
    public function addNotice(string $message, ?string $header = null, array $options = []): self
    {
        return $this->addMessageInternal(FlashMessage::NOTICE, $message, $header, $options);
    }
    
    /**
     * Adds a new flash message of type "WARNING" to the stack
     *
     * @param   string       $message  The message to be shown
     * @param   string|null  $header   The header of the message
     * @param   array        $options  Additional options when creating your message
     *                                 - queueId string (core.template.flashMessages) the stack identifier to
     *                                 add this message to.
     *                                 - storeInSession bool (FALSE) If set to true the message will be stored in the
     *                                 session instead of the local context. If the message is stored in the session it
     *                                 survives page changes and redirects.
     *
     * @return $this
     */
    public function addWarning(string $message, ?string $header = null, array $options = []): self
    {
        return $this->addMessageInternal(FlashMessage::WARNING, $message, $header, $options);
    }
    
    /**
     * Adds a new flash message of type "OK" to the stack
     *
     * @param   string       $message  The message to be shown
     * @param   string|null  $header   The header of the message
     * @param   array        $options  Additional options when creating your message
     *                                 - queueId string (core.template.flashMessages) the stack identifier to
     *                                 add this message to.
     *                                 - storeInSession bool (FALSE) If set to true the message will be stored in the
     *                                 session instead of the local context. If the message is stored in the session it
     *                                 survives page changes and redirects.
     *
     * @return $this
     */
    public function addOk(string $message, ?string $header = null, array $options = []): self
    {
        return $this->addMessageInternal(FlashMessage::OK, $message, $header, $options);
    }
    
    /**
     * Adds a new flash message of type "INFO" to the stack
     *
     * @param   string       $message  The message to be shown
     * @param   string|null  $header   The header of the message
     * @param   array        $options  Additional options when creating your message
     *                                 - queueId string (core.template.flashMessages) the stack identifier to
     *                                 add this message to.
     *                                 - storeInSession bool (FALSE) If set to true the message will be stored in the
     *                                 session instead of the local context. If the message is stored in the session it
     *                                 survives page changes and redirects.
     *
     * @return $this
     */
    public function addInfo(string $message, ?string $header = null, array $options = []): self
    {
        return $this->addMessageInternal(FlashMessage::INFO, $message, $header, $options);
    }
    
    /**
     * Adds a new flash message of type "ERROR" to the stack
     *
     * @param   string       $message  The message to be shown
     * @param   string|null  $header   The header of the message
     * @param   array        $options  Additional options when creating your message
     *                                 - queueId string (core.template.flashMessages) the stack identifier to
     *                                 add this message to.
     *                                 - storeInSession bool (FALSE) If set to true the message will be stored in the
     *                                 session instead of the local context. If the message is stored in the session it
     *                                 survives page changes and redirects.
     *
     * @return $this
     */
    public function addError(string $message, ?string $header = null, array $options = []): self
    {
        return $this->addMessageInternal(FlashMessage::ERROR, $message, $header, $options);
    }
    
    /**
     * Can be used to render the stack of flash messages to a variable.
     *
     * @param   string|null  $queueId  The query id of messages to render, or null for the default queue
     *
     * @return string
     */
    public function renderMessages(?string $queueId = null): string
    {
        $queue = $this->flashMessageService->getMessageQueueByIdentifier($queueId ?? $this->defaultQueueId);
        
        return $this->rendererResolver->resolve()->render($queue->getAllMessagesAndFlush());
    }
    
    /**
     * Returns true if there are currently messages in the queue false if not
     *
     * @param   string|null  $queueId  The query id of messages to check, or null for the default queue
     *
     * @return bool
     */
    public function hasMessages(?string $queueId = null): bool
    {
        // ->count() will always return 0 here, which does not help us in any way...
        return count($this->getQueue($queueId)->getAllMessages()) > 0;
    }
    
    /**
     * Returns the queue object for either a selected queue id or the default queue
     *
     * @param   string|null  $queueId  The query id of messages to retrieve, or null for the default queue
     *
     * @return \TYPO3\CMS\Core\Messaging\FlashMessageQueue
     */
    public function getQueue(?string $queueId = null): FlashMessageQueue
    {
        return $this->flashMessageService->getMessageQueueByIdentifier($queueId ?? $this->defaultQueueId);
    }
    
    /**
     * Internal helper which is used to validate the given options and to create the message instance on a certain
     * stack.
     *
     * @param   int          $type
     * @param   string       $message
     * @param   string|null  $header
     * @param   array        $options
     *
     * @return $this
     */
    protected function addMessageInternal(int $type, string $message, ?string $header, array $options): self
    {
        // Prepare the options
        $options = Options::make($options, [
            'storeInSession' => [
                'type' => 'bool',
                'default' => false,
            ],
            'queueId' => [
                'type' => 'string',
                'default' => $this->defaultQueueId,
            ],
        ]);
        
        // Create the new message
        $message = $this->makeInstance(
            FlashMessage::class,
            [
                $message,
                $header ?? '',
                $type,
                $options['storeInSession'],
            ]
        );
        
        // Get the stack
        $stack = $this->flashMessageService->getMessageQueueByIdentifier($options['queueId']);
        $stack->addMessage($message);
        
        // Done
        return $this;
    }
}
