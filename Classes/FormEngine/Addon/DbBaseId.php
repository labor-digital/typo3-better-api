<?php
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
 * Last modified: 2020.03.18 at 15:38
 */

namespace LaborDigital\T3BA\FormEngine\Addon;

use GuzzleHttp\Psr7\Query;
use LaborDigital\T3BA\Event\FormEngine\BackendFormNodePostProcessorEvent;
use TYPO3\CMS\Backend\Form\NodeExpansion\FieldWizard;

class DbBaseId
{

    /**
     * This element adds the basePid constraints to the javascript of the element browser
     *
     * @param   \LaborDigital\T3BA\Event\FormEngine\BackendFormNodePostProcessorEvent  $event
     */
    public static function onPostProcess(BackendFormNodePostProcessorEvent $event): void
    {
        $config = $event->getProxy()->getConfig();
        $result = $event->getResult();

        // Check if there is work for us to do
        if (empty($result) || empty($result['html'])) {
            return;
        }

        // We only apply this fix for the field wizard
        if (! $event->getNode() instanceof FieldWizard) {
            return;
        }

        // Ignore if there is already a temp mount set
        $html = $result['html'];
        if (stripos($html, 'setTempDBmount') !== false || stripos($html, 'expandPage') !== false
            || stripos($html, 'data-params="') === false) {
            return;
        }

        // Inject the temp db mount based on the basePid
        $pattern = '~(data-params=".*?\|)([^|]*?)(")~i';
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

                $url = Query::build([
                    'expandPage'     => $pid,
                    'setTempDBmount' => $pid,
                ]);

                return $prefix . $table . '&' . $url . $suffix;
            }, $html);

            $event->setResult($result);

            return;

        }

        // Make sure to reset the temp db mount if multiple fields are registered in a form
        $result['html'] = preg_replace_callback($pattern, static function ($m) {
            [$a, $prefix, $table, $suffix] = $m;
            $url = Query::build([
                'setTempDBmount' => 0,
            ]);

            return $prefix . $table . '&' . $url . $suffix;
        }, $html);

        $event->setResult($result);

    }
}
