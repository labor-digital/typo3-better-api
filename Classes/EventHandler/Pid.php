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
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class Pid implements LazyEventSubscriberInterface
{
    /**
     * @var \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     */
    protected $context;
    
    /**
     * PidEventHandler constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\TypoContext\TypoContext       $context
     * @param   \LaborDigital\T3ba\Tool\TypoScript\TypoScriptService  $typoScriptService
     */
    public function __construct(
        TypoContext $context
    )
    {
        $this->context = $context;
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
        
        if (empty($pidConfig)) {
            return;
        }
        
        $diff = $this->generateDiff($this->context->pid()->getAll(), $pidConfig);
        
        if (empty($diff)) {
            return;
        }
        
        $this->context->pid()->setMultiple($diff);
    }
    
    /**
     * Generates a diff between the currently set pids and the typo script pids
     * Only if the diff detects a difference the pids will be updated
     *
     * @param   array  $a
     * @param   array  $b
     *
     * @return array
     */
    protected function generateDiff(array $a, array $b): array
    {
        $diff = [];
        
        foreach ($b as $k => $v) {
            $_k = rtrim($k, '.');
            
            if (is_array($v)) {
                $diff[$_k] = $this->generateDiff(is_array($a[$_k] ?? null) ? $a[$_k] : [], $v);
                continue;
            }
            
            if (($a[$_k] ?? null) === (int)$v) {
                continue;
            }
            
            $diff[$_k] = (int)$v;
        }
        
        return array_filter($diff);
    }
}
