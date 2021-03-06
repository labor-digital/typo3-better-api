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

namespace LaborDigital\T3ba\Tool\Simulation\Pass;


use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;

class SiteSimulationPass implements SimulatorPassInterface
{
    use TypoContextAwareTrait;
    
    /**
     * @inheritDoc
     */
    public function addOptionDefinition(array $options): array
    {
        $options['site'] = [
            'type' => ['string', 'null'],
            'default' => null,
        ];
        
        return $options;
    }
    
    /**
     * @inheritDoc
     */
    public function requireSimulation(array $options, array &$storage): bool
    {
        return $options['site'] !== null
               && $options['pid'] === null
               && (
                   ! $this->getTypoContext()->site()->hasCurrent()
                   || $this->getTypoContext()->site()->getCurrent()->getIdentifier() !== $options['site']
               );
    }
    
    /**
     * @inheritDoc
     */
    public function setup(array $options, array &$storage): void
    {
        // Backup the current site
        $storage['site'] = $this->getTypoContext()->config()->getRequestAttribute('site');
        
        // Find the given site instance and inject it into the request
        $site = $this->getTypoContext()->site()->get($options['site']);
        $this->getTypoContext()->config()->setRequestAttribute('site', $site);
    }
    
    /**
     * @inheritDoc
     */
    public function rollBack(array $storage): void
    {
        $this->getTypoContext()->config()->setRequestAttribute('site', $storage['site']);
    }
    
}
