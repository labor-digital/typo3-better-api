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
 * Last modified: 2020.03.18 at 16:24
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\Addon;


use LaborDigital\Typo3BetterApi\Event\Events\BackendFormFilterEvent;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class FixFlexFormSectionDefinitionApplier implements LazyEventSubscriberInterface {
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
	 */
	protected $context;
	
	/**
	 * FixFlexFormSectionDefinitionApplier constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\TypoContext\TypoContext $context
	 */
	public function __construct(TypoContext $context) {
		$this->context = $context;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function subscribeToEvents(EventSubscriptionInterface $subscription) {
		$subscription->subscribe(BackendFormFilterEvent::class, "__apply", ["priority" => -10]);
	}
	
	/**
	 * This addon fixes a bug, where the form engine is not able to render flex form sections
	 * without saving the record at least once. This is because it removes the flex form sections
	 * from the TCA because they will never end up in the "processedTca" array.
	 *
	 * This method will forcefully add the data structure identifier column to the list of processed columns
	 * which stop's typo3 from removing it in the processed tca array
	 *
	 * @param \LaborDigital\Typo3BetterApi\Event\Events\BackendFormFilterEvent $event
	 */
	public function __apply(BackendFormFilterEvent $event) {
		$data = $event->getData();
		if ($data["command"] !== "new") return;
		$fieldName = $this->context->getRequestAspect()->getPost("dataStructureIdentifier.fieldName");
		if (empty($fieldName)) return;
		$data["columnsToProcess"][] = $fieldName;
		$event->setData($data);
	}
}