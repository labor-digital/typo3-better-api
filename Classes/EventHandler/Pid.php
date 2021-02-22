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
 * Last modified: 2020.08.25 at 19:36
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\EventHandler;


use LaborDigital\T3BA\Event\TypoScript\ConfigArrayPostProcEvent;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use LaborDigital\T3BA\Tool\TypoScript\TypoScriptService;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class Pid implements LazyEventSubscriberInterface
{
    /**
     * @var \LaborDigital\T3BA\Tool\TypoContext\TypoContext
     */
    protected $context;

    /**
     * @var \LaborDigital\T3BA\Tool\TypoScript\TypoScriptService
     */
    protected $typoScriptService;

    /**
     * PidEventHandler constructor.
     *
     * @param   \LaborDigital\T3BA\Tool\TypoContext\TypoContext       $context
     * @param   \LaborDigital\T3BA\Tool\TypoScript\TypoScriptService  $typoScriptService
     */
    public function __construct(
        TypoContext $context,
        TypoScriptService $typoScriptService
    ) {
        $this->context           = $context;
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
     * @param   \LaborDigital\T3BA\Event\TypoScript\ConfigArrayPostProcEvent  $event
     *
     */
    public function onTypoScriptConfigPostProcessing(ConfigArrayPostProcEvent $event): void
    {
        $pidConfig = Arrays::getPath($event->getConfig(), ['t3ba.', 'pid.'], []);
        $pidConfig = $this->typoScriptService->removeDots($pidConfig);
        $this->context->pid()->setMultiple($pidConfig);
    }
}
