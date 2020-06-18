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
 * Last modified: 2020.03.18 at 13:48
 */

namespace LaborDigital\Typo3BetterApi\Pid;

use LaborDigital\Typo3BetterApi\Event\Events\TcaCompletelyLoadedEvent;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class PidTcaFilter implements LazyEventSubscriberInterface
{
    
    
    /**
     * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
     */
    protected $context;
    
    /**
     * PidTcaFilter constructor.
     *
     * @param   \LaborDigital\Typo3BetterApi\TypoContext\TypoContext  $context
     */
    public function __construct(TypoContext $context)
    {
        $this->context = $context;
    }
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(TcaCompletelyLoadedEvent::class, '__replaceAllPidInTca', ['priority' => 100]);
    }
    
    /**
     * This filter is used to traverse the tca array and replace all $pid.selector and (at)pid.selector
     * references in strings it can find with the actual page id it stands for.
     */
    public function __replaceAllPidInTca()
    {
        // Prepare the placeholder list
        $placeholders = [];
        foreach (Arrays::flatten($this->context->getPidAspect()->getAllPids()) as $k => $v) {
            $placeholders['$pid.' . $k] = $v;
            $placeholders['@pid.' . $k] = $v;
        }
        $placeholders = Arrays::sortByKeyStrLen($placeholders);
        $find         = array_keys($placeholders);
        $replace      = array_values($placeholders);
        
        // Skip if there is no tca
        if (! is_array($GLOBALS['TCA'])) {
            return;
        }
        
        // Walker to traverse the tca array
        $walker = function (array &$list, callable $walker) use ($find, $replace) {
            foreach ($list as $k => &$v) {
                if (is_array($v)) {
                    $walker($v, $walker);
                } elseif (is_string($v)) {
                    $v = str_ireplace($find, $replace, $v);
                }
            }
        };
        
        // Start the walker
        $walker($GLOBALS['TCA'], $walker);
    }
}
