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
 * Last modified: 2020.08.23 at 23:23
 */
declare(strict_types=1);

namespace LaborDigital\T3BA\Core\Util;

use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;

trait CTypeRegistrationTrait
{

    /**
     * A mostly internal helper that is used to inject a given list of elements as cTypes in the tt_content TCA array
     *
     * @param   array  $tca       the TCA array
     * @param   array  $elements  A list of arrays that hold four values each.
     *                            The values are [$sectionLabel, $title, $signature, $icon]
     */
    protected function registerCTypesForElements(array &$tca, array $elements): void
    {
        // Get the correct slot in the tca
        $itemList = Arrays::getPath($tca, ['tt_content', 'columns', 'CType', 'config', 'items'], []);

        // Build the section list from all entries
        $sectionList = [];
        $options     = [];
        foreach (array_reverse($itemList) as $k => $item) {
            if ($item[1] === '--div--') {
                $sectionId               = Inflector::toUuid($item[0]);
                $sectionList[$sectionId] = [
                    'item'    => $item,
                    'options' => array_reverse($options),
                ];
                $options                 = [];
                continue;
            }
            $options[] = $item;
        }
        $sectionList = array_reverse($sectionList);

        // Process the elements
        foreach ($elements as $element) {
            // Prepare the input
            [$sectionLabel, $title, $signature, $icon] = array_values($element);

            // Find the section id
            $sectionId = Inflector::toUuid($sectionLabel);

            // Create a new section if it does not exist
            if (! isset($sectionList[$sectionId])) {
                $sectionList[$sectionId] = [
                    'item'    => [
                        $sectionLabel,
                        '--div--',
                    ],
                    'options' => [],
                ];
            }

            // Create the element
            $sectionList[$sectionId]['options'][] = [
                $title,
                $signature,
                $icon,
            ];
        }

        // Rebuild the list
        $newItemList = [];
        foreach ($sectionList as $section) {
            if (empty($section['options'])) {
                continue;
            }
            $newItemList[] = $section['item'];
            foreach ($section['options'] as $option) {
                $newItemList[] = $option;
            }
        }
        $tca = Arrays::setPath($tca, ['tt_content', 'columns', 'CType', 'config', 'items'], $newItemList);
    }
}
