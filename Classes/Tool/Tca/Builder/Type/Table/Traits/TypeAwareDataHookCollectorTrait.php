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
 * Last modified: 2021.01.28 at 19:15
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Traits;


use LaborDigital\T3BA\Tool\DataHook\DataHookTypes;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaField;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTableType;

/**
 * Trait TypeAwareDataHookCollectorTrait
 *
 * IMPORTANT: Use this trait only if your class already used the DataHookCollectorTrait
 *
 * @package LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Traits
 * @see     \LaborDigital\T3BA\Tool\DataHook\DataHookCollectorTrait
 */
trait TypeAwareDataHookCollectorTrait
{

    /**
     * @inheritDoc
     */
    protected function getDataHookTableFieldConstraints(): array
    {
        if ($this instanceof TcaField || $this instanceof TcaTableType) {
            $typeCol = $this->getForm()->getForm()->getTypeColumn();

            if (empty($typeCol)) {
                return [];
            }

            return [$typeCol => $this->getForm()->getTypeName()];
        }

        return [];
    }

    protected function loadDataHooksBasedOnType(array &$tca): void
    {
        if (isset($tca[DataHookTypes::TCA_DATA_HOOK_KEY])) {
            $definition = $tca[DataHookTypes::TCA_DATA_HOOK_KEY];
            unset($tca[DataHookTypes::TCA_DATA_HOOK_KEY]);

            dbge($definition);

        }
    }
}
