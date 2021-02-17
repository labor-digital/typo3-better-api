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
 * Last modified: 2020.08.24 at 21:44
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig\Abstracts;


use LaborDigital\T3BA\ExtConfig\Interfaces\ExtConfigApplierInterface;
use Neunerlei\Configuration\State\ConfigState;

abstract class AbstractExtConfigApplier implements ExtConfigApplierInterface
{

    /**
     * @var ConfigState
     */
    protected $state;

    /**
     * @inheritDoc
     */
    public function injectState(ConfigState $state): void
    {
        $this->state = $state;
    }

}
