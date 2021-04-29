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
 * Last modified: 2020.08.25 at 15:26
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig\Abstracts;


use LaborDigital\T3BA\ExtConfig\Interfaces\ExtConfigConfiguratorInterface;
use LaborDigital\T3BA\ExtConfig\Interfaces\ExtConfigContextAwareInterface;
use LaborDigital\T3BA\ExtConfig\Traits\ExtConfigContextAwareTrait;
use Neunerlei\Configuration\State\ConfigState;

abstract class AbstractExtConfigConfigurator implements
    ExtConfigContextAwareInterface, ExtConfigConfiguratorInterface
{
    use ExtConfigContextAwareTrait;
    
    /**
     * Helper to automatically inject all configurator properties into the given state object
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $state
     */
    protected function autoFinish(ConfigState $state): void
    {
        $vars = get_object_vars($this);
        unset($vars['context']);
        $state->setMultiple($vars);
    }
    
    /**
     * @inheritDoc
     */
    public function finish(ConfigState $state): void
    {
        $this->autoFinish($state);
    }
}
