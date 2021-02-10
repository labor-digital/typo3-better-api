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
 * Last modified: 2021.02.09 at 18:56
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Io\Traits;


use LaborDigital\T3BA\Tool\Tca\Builder\Logic\FormElementContainingInterface;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Flex;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\FlexTab;

trait FactoryPopulatorTrait
{
    /**
     * Creates the child instances of the flex form based on the given definition array
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Flex  $flex
     * @param   array                                                   $def
     */
    protected function populateElements(Flex $flex, array $def): void
    {
        if (! empty($def['meta']) && is_array($def['meta'])) {
            $flex->setMeta($def['meta']);
        }

        foreach ($def['sheets'] ?? [] as $sheetId => $sheet) {
            $tab = $flex->getTab($sheetId);
            $this->populateTab($flex, $tab, $sheet);
        }
    }

    /**
     * Creates the child instances of the form based on the given sheet definition
     *
     * @param   Flex     $flex
     * @param   FlexTab  $tab
     * @param   array    $sheet
     */
    protected function populateTab(Flex $flex, FlexTab $tab, array $sheet): void
    {
        // Make sure the "ROOT" node exists
        if (! isset($sheet['ROOT']) || ! is_array($sheet['ROOT'])) {
            if (isset($sheet['el']) && is_array($sheet['el'])) {
                $sheet = ['ROOT' => $sheet];
            }
        }

        $label = $sheet['ROOT']['TCEforms']['sheetTitle'] ?? null;
        if (! empty($label)) {
            $tab->setLabel($label);
        }

        $displayCond = $sheet['ROOT']['TCEforms']['displayCond'] ?? null;
        if (! empty($displayCond)) {
            $tab->setDisplayCondition($displayCond);
        }

        foreach ($sheet['ROOT']['el'] ?? [] as $k => $el) {
            // Sections
            if (! empty($el['section'])) {
                $this->populateSection($flex, $tab, $k, $el);
                continue;
            }

            // Fields
            $fEl = $this->prepareFieldConfig($el);
            if ($fEl !== null) {
                $this->populateField($flex, $tab, $k, $fEl);
            }
        }
    }

    /**
     * Creates and populates a new section / container instance in the flex form object
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Flex     $flex
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\FlexTab  $tab
     * @param   string                                                     $id
     * @param   array                                                      $config
     */
    protected function populateSection(Flex $flex, FlexTab $tab, string $id, array $config): void
    {
        $tab->addMultiple(function () use ($flex, $id, $config) {
            $children = $config['el'] ?? [];
            if (empty($children) || ! is_array($children)) {
                return;
            }

            $container = reset($children);
            if (empty($container['el']) || ! is_array($container['el'])) {
                return;
            }

            $i = $flex->getSection($id);
            $i->setContainerItemId((string)key($children));

            // Load section labels
            if (! empty($config['title'])) {
                $i->setLabel((string)$config['title']);
            }
            if (! empty($container['title'])) {
                $i->setContainerItemLabel((string)$container['title']);
            } elseif (! empty($container['tx_templavoila']['title'])) {
                $i->setContainerItemLabel((string)$container['tx_templavoila']['title']);
            }

            foreach ($container['el'] as $k => $el) {
                $fEl = $this->prepareFieldConfig($el);
                if ($fEl !== null) {
                    $this->populateField($flex, $i, $k, $fEl);
                }
            }
        });
    }

    /**
     * Internal helper to create a new field in the form instance with the provided config applied to it.
     *
     * @param   Flex                            $flex
     * @param   FormElementContainingInterface  $target
     * @param   string                          $id
     * @param   array                           $config
     */
    protected function populateField(
        Flex $flex,
        FormElementContainingInterface $target,
        string $id,
        array $config
    ): void {
        $target->addMultiple(static function () use ($flex, $id, $config) {
            $i = $flex->getField($id);
            $i->setRaw($config);
        });
    }

    /**
     * Checks if the given configuration array can be used as field configuration.
     * It will either return the field configuration or null if the config is no valid field.
     *
     * @param   array  $el
     *
     * @return array|null
     */
    protected function prepareFieldConfig(array $el): ?array
    {
        if (! empty($el['TCEforms'])) {
            return $el['TCEforms'];
        }

        // Fallback for invalid field configuration
        if (isset($el['config']['type']) || isset($el['config']['renderType'])) {
            return $el;
        }

        return null;
    }
}
