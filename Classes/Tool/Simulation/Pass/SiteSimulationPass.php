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
        $hasCurrentSite = $this->getTypoContext()->site()->hasCurrent();
        $requiresSite = $options['site'] !== null;
        $requiresPid = ($options['pid'] ?? null) !== null;
        
        if (
            // If a site is required...
            $requiresSite
            && (
                // ... and a current site is available, but does not match the required site...
                (
                    $hasCurrentSite
                    && $this->getTypoContext()->site()->getCurrent()->getIdentifier() !== $options['site']
                )
                // ... or there is currently no site...
                || ! $hasCurrentSite
            )
        ) {
            // ...simulate the required site
            return true;
        }
        
        if (
            // If a pid is required...
            $requiresPid
            // ... but NOT a site...
            && ! $requiresSite
            // ... but there is no current site...
            && ! $hasCurrentSite
        ) {
            // ... simulate the required site based on the given pid
            $storage['pid'] = $options['pid'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * @inheritDoc
     */
    public function setup(array $options, array &$storage): void
    {
        // Backup the current site
        $storage['site'] = $this->getTypoContext()->config()->getRequestAttribute('site');
        
        // Find the given site instance and inject it into the request
        if (isset($storage['pid'])) {
            $site = $this->getTypoContext()->site()->getForPid($storage['pid']);
        } else {
            $site = $this->getTypoContext()->site()->get($options['site']);
        }
        
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
