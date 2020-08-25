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
use TYPO3\CMS\Backend\Form\NodeExpansion\FieldWizard;
use function GuzzleHttp\Psr7\build_query;

class DbBaseIdApplier implements LazyEventSubscriberInterface
{

    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(BackendFormNodePostProcessorEvent::class, '__onPostProcess');
    }

    /**
     * This element adds the basePid and limitToBasePid constraints to the javascript of the element browser
     *
     * @param   \LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodePostProcessorEvent  $event
     */
    public function __onPostProcess(BackendFormNodePostProcessorEvent $event)
    {
        $data   = $event->getProxy()->getProperty('data');
        $config = Arrays::getPath($data, ['parameterArray', 'fieldConf', 'config'], []);
        $result = $event->getResult();

        // Translate legacy config
        if (! isset($config['basePid'])) {
            $config['basePid'] = $config['rootPage'];
        }

        // Check if there is work for us to do
        if (empty($result) || empty($result['html'])) {
            return;
        }

        // We only apply this fix for the field wizard
        if (! $event->getNode() instanceof FieldWizard) {
            return;
        }

        // Ignore if there is already a tempmount set
        $html = $result['html'];
        if (stripos($html, 'setTempDBmount') !== false || stripos($html, 'expandPage') !== false
            || stripos($html, 'setFormValueOpenBrowser') === false) {
            return;
        }

        // Inject the temp db mount based on the basePid
        $pattern = '~(setFormValueOpenBrowser\(\'db\'[^"]*?\|\|\|(.*?))(\'\);\s?return false;)~i';
        if (! empty($config['basePid'])) {
            // Use the numeric pid as default pid
            $pidMap = $config['basePid'];
            if (! is_array($pidMap)) {
                $pidMap = ['@default' => $config['basePid']];
            }

            // Rewrite the object html
            $result['html'] = preg_replace_callback($pattern, function ($m) use ($pidMap) {
                [$a, $prefix, $table, $suffix] = $m;
                $pid = $pidMap[$table] ?? $pidMap['@default'] ?? 0;
                $url = build_query([
                    'expandPage'     => $pid,
                    'setTempDBmount' => $pid,
                ]);

                return $prefix . '&' . $url . $suffix;
            }, $html);

            $event->setResult($result);

            return;

        }

        // Make sure to reset the temp db mount if multiple fields are registered in a form
        $result['html'] = preg_replace_callback($pattern, static function ($m) {
            [$a, $prefix, $table, $suffix] = $m;
            $url = build_query([
                'setTempDBmount' => 0,
            ]);

            return $prefix . '&' . $url . $suffix;
        }, $html);
        $event->setResult($result);

    }
}
