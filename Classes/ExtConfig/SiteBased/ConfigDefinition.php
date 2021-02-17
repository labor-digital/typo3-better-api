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
 * Last modified: 2021.02.16 at 18:15
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig\SiteBased;


use LaborDigital\T3BA\ExtConfig\Interfaces\SiteKeyProviderInterface;
use Neunerlei\Configuration\Loader\ConfigDefinition as DefaultConfigDefinition;
use Neunerlei\Configuration\State\ConfigState;
use TYPO3\CMS\Core\Site\Entity\Site;

class ConfigDefinition extends DefaultConfigDefinition
{
    /**
     * @var \TYPO3\CMS\Core\Site\Entity\Site[]
     */
    protected $sites;

    /**
     * @inheritDoc
     */
    public function __construct(
        DefaultConfigDefinition $baseDefinition,
        array $sites
    ) {
        parent::__construct(
            $baseDefinition->handlerDefinition,
            $baseDefinition->configContext,
            $baseDefinition->configClasses,
            $baseDefinition->overrideConfigClasses,
            $baseDefinition->classNamespaceMap
        );
        $this->sites = $sites;
    }

    /**
     * @inheritDoc
     */
    public function process(): void
    {
        foreach ($this->sites as $siteKey => $site) {
            $this->runWithSiteBasedDefinition(
                $siteKey, $site,
                function () {
                    parent::process();
                }
            );
        }
    }

    /**
     * Internal helper to run the "process" method for a single site
     *
     * @param   string                            $siteKey
     * @param   \TYPO3\CMS\Core\Site\Entity\Site  $site
     * @param   callable                          $callback
     */
    protected function runWithSiteBasedDefinition(string $siteKey, Site $site, callable $callback): void
    {
        $clone = clone $this;

        if ($this->configContext instanceof SiteConfigContext) {
            $this->configContext->initializeSite($siteKey, $site);
        }

        $state     = $this->configContext->getState();
        $siteState = new ConfigState([]);

        $this->configContext->initialize($this->configContext->getLoaderContext(), $siteState);

        $siteKeys          = array_keys($this->sites);
        $siteConfigClasses = [];
        foreach ($this->configClasses as $class) {
            // Allow filtering of the configuration based on a site key
            if (in_array(SiteKeyProviderInterface::class, class_implements($class), true)) {
                $result = $class::getSiteKeys($siteKeys);
                if (! empty($result) && ! in_array($siteKey, $result, true)) {
                    continue;
                }
            }

            $siteConfigClasses[] = $class;
        }

        $filter = static function (array $value) use ($siteConfigClasses) {
            return array_filter($value, static function ($key) use ($siteConfigClasses) {
                return in_array($key, $siteConfigClasses, true);
            }, ARRAY_FILTER_USE_KEY);
        };

        $this->configClasses         = $siteConfigClasses;
        $this->overrideConfigClasses = $filter($this->overrideConfigClasses);
        $this->classNamespaceMap     = $filter($this->classNamespaceMap);

        try {
            $callback();
        } finally {
            // Revert the context back to the initial state
            $this->configContext->initialize($this->configContext->getLoaderContext(), $state);
            $this->configClasses         = $clone->configClasses;
            $this->overrideConfigClasses = $clone->overrideConfigClasses;
            $this->classNamespaceMap     = $clone->classNamespaceMap;
        }

        // Inject the site state into the main state object
        $data = $siteState->getAll();

        // Special handling for the "root" node -> this allows to configure non-site-based configuration
        if (isset($data['root']) && is_array($data['root'])) {
            $state->importFrom(new ConfigState($data['root']));
            unset($data['root']);
        }

        $state->set('typo.site.' . $siteKey, $data);
    }


}
