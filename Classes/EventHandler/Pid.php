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


namespace LaborDigital\T3ba\EventHandler;


use LaborDigital\T3ba\Event\TypoScript\ConfigArrayPostProcEvent;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use LaborDigital\T3ba\Tool\TypoScript\TypoScriptService;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class Pid implements LazyEventSubscriberInterface
{
    /**
     * @var \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     */
    protected $context;
    
    /**
     * @var \LaborDigital\T3ba\Tool\TypoScript\TypoScriptService
     */
    protected $typoScriptService;
    
    /**
     * PidEventHandler constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\TypoContext\TypoContext       $context
     * @param   \LaborDigital\T3ba\Tool\TypoScript\TypoScriptService  $typoScriptService
     */
    public function __construct(
        TypoContext $context,
        TypoScriptService $typoScriptService
    )
    {
        $this->context = $context;
        $this->typoScriptService = $typoScriptService;
    }
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(ConfigArrayPostProcEvent::class, 'onTypoScriptConfigPostProcessing');
    }
    
    /**
     * Reads the pids from typo script and re-injects their values into the pid aspect.
     * This allows the pid aspect to be modified using typoScript
     *
     * @param   \LaborDigital\T3ba\Event\TypoScript\ConfigArrayPostProcEvent  $event
     *
     */
    public function onTypoScriptConfigPostProcessing(ConfigArrayPostProcEvent $event): void
    {
        $pidConfig = $event->getConfig()['t3ba.']['pid.'] ?? [];
        $pidConfig = $this->typoScriptService->removeDots($pidConfig);
        $this->context->pid()->setMultiple($pidConfig);
    }
}
