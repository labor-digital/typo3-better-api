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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\StateAwareTcaPostProcessorInterface;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\StateAwareTcaPostProcessorTrait;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\TcaPostProcessorStepInterface;
use Neunerlei\Configuration\State\ConfigState;

/**
 * Class CshLabelStep
 *
 * Extracts the registered information for the context sensitive help registration
 *
 * @package LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step
 */
class CshLabelStep implements TcaPostProcessorStepInterface, StateAwareTcaPostProcessorInterface, NoDiInterface
{
    public const CONFIG_KEY = 'cshLabels';
    
    protected $labels = [];
    
    /**
     * @inheritDoc
     */
    public function process(string $tableName, array &$config, array &$meta): void
    {
        if (! empty($config['ctrl'][static::CONFIG_KEY]) && is_array($config['ctrl'][static::CONFIG_KEY])) {
            foreach ($config['ctrl'][static::CONFIG_KEY] as $label) {
                $this->labels[] = [$tableName, $label];
                // @todo remove this in the next major version
                $meta['cshLabels'][] = [$tableName, $label];
            }
        }
        
        unset($config['ctrl'][static::CONFIG_KEY]);
    }
    
    public function applyToConfigState(ConfigState $state): void
    {
        $state->set('tca.cshLabels', $this->labels);
    }
    
    
}