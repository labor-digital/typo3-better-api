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
 * Last modified: 2020.07.16 at 20:43
 */

declare(strict_types=1);


namespace LaborDigital\Typo3BetterApi\Simulation\Pass;


use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;

class SiteSimulationPass implements SimulatorPassInterface
{
    /**
     * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
     */
    protected $typoContext;

    /**
     * SiteSimulationPass constructor.
     *
     * @param   \LaborDigital\Typo3BetterApi\TypoContext\TypoContext  $typoContext
     */
    public function __construct(TypoContext $typoContext)
    {
        $this->typoContext = $typoContext;
    }

    /**
     * @inheritDoc
     */
    public function addOptionDefinition(array $options): array
    {
        $options['site'] = [
            'type'    => ['string', 'null'],
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
                   ! $this->typoContext->Site()->hasCurrent()
                   || $this->typoContext->Site()->getCurrent()->getIdentifier() !== $options['site']
               );
    }

    /**
     * @inheritDoc
     */
    public function setup(array $options, array &$storage): void
    {
        // Backup the current site
        $storage['site'] = $this->typoContext->Config()->getRequestAttribute('site');

        // Find the given site instance and inject it into the request
        $site = $this->typoContext->Site()->get($options['site']);
        $this->typoContext->Config()->setRequestAttribute('site', $site);
    }

    /**
     * @inheritDoc
     */
    public function rollBack(array $storage): void
    {
        $this->typoContext->Config()->setRequestAttribute('site', $storage['site']);
    }

}
