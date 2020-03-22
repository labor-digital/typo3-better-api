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
 * Last modified: 2020.03.18 at 15:38
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\Addon;


use LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodePostProcessorEvent;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use function GuzzleHttp\Psr7\build_query;

class DbBaseIdApplier implements LazyEventSubscriberInterface {
	
	/**
	 * @inheritDoc
	 */
	public static function subscribeToEvents(EventSubscriptionInterface $subscription) {
		$subscription->subscribe(BackendFormNodePostProcessorEvent::class, "__onPostProcess");
	}
	
	/**
	 * This element adds the basePid and limitToBasePid constraints to the javascript of the element browser
	 *
	 * @param \LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodePostProcessorEvent $event
	 */
	public function __onPostProcess(BackendFormNodePostProcessorEvent $event) {
		
		/** @var \LaborDigital\Typo3BetterApi\BackendForms\Addon\FormNodeEventProxy $renderer */
		$config = Arrays::getPath($event->getProxy()->getProperty("data"), ["parameterArray", "fieldConf", "config"], []);
		$result = $event->getResult();
		
		// Translate legacy config
		if (!isset($config["basePid"])) $config["basePid"] = $config["rootPage"];
		if (!isset($config["limitToBasePid"])) $config["limitToBasePid"] = $config["limitToRootPage"];
		
		// Check if there is work for us to do
		if (is_null($config["basePid"]) && is_null($config["limitToBasePid"]) || empty($result["html"])) return;
		
		// Build the expanded js query so we can tell the js window about our configuration
		$params = [];
		if (is_numeric($config["basePid"])) $params["expandPage"] = (int)$config["basePid"];
		if (isset($params["expandPage"]) && $config["limitToBasePid"]) $params["setTempDBmount"] = $params["expandPage"];
		$url = "&" . build_query($params);
		
		// Update the open browser script
		if (stripos($result["html"], $url) !== FALSE) return;
		$result["html"] = (string)preg_replace("~(setFormValueOpenBrowser\('db'[^\"]*?)('\);\s?return false;)~si", "$1$url$2", $result["html"]);
		$event->setResult($result);
		
	}
}