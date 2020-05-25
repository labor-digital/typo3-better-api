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
 * Last modified: 2020.03.19 at 13:54
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\Addon;

use LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodeFilterEvent;
use LaborDigital\Typo3BetterApi\Translation\TranslationService;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class FieldDefaultAndPlaceholderTranslationApplier implements LazyEventSubscriberInterface
{
    /**
     * @var \LaborDigital\Typo3BetterApi\Translation\TranslationService
     */
    protected $translationService;
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(BackendFormNodeFilterEvent::class, '__onNodeDataFilter');
    }
    
    /**
     * FieldDefaultValueTranslationApplier constructor.
     *
     * @param \LaborDigital\Typo3BetterApi\Translation\TranslationService $translationService
     */
    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }
    
    /**
     * This applier is used to translate the "default" value of form elements.
     *
     * @param \LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodeFilterEvent $event
     */
    public function __onNodeDataFilter(BackendFormNodeFilterEvent $event)
    {
        $data = $event->getProxy()->getProperty('data');
        
        // Translate the default value
        $default = Arrays::getPath($data, ['parameterArray', 'fieldConf', 'config', 'default']);
        if (is_string($default) && Arrays::getPath($data, 'parameterArray.itemFormElValue') === $default) {
            $data = Arrays::setPath($data, 'parameterArray.itemFormElValue', $this->translationService->translateMaybe($default));
        }
        
        // Translate the placeholder
        $placeholder = Arrays::getPath($data, ['parameterArray', 'fieldConf', 'config', 'placeholder']);
        if (is_string($placeholder)) {
            $data = Arrays::setPath($data, 'parameterArray.fieldConf.config.placeholder', $this->translationService->translateMaybe($placeholder));
        }
        
        // Update the default value in the configuration
        $event->getProxy()->setProperty('data', $data);
    }
}
