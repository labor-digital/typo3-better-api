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


namespace LaborDigital\T3ba\ExtConfig\SiteBased;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Event\ExtConfig\SingleSiteBasedExtConfigGeneratedEvent;
use LaborDigital\T3ba\Event\ExtConfig\SiteBasedExtConfigGeneratedEvent;
use LaborDigital\T3ba\ExtConfig\Interfaces\ExtendedSiteBasedHandlerInterface;
use LaborDigital\T3ba\ExtConfig\Interfaces\SiteIdentifierProviderInterface;
use LaborDigital\T3ba\ExtConfig\Interfaces\SiteKeyProviderInterface;
use Neunerlei\Configuration\Loader\ConfigDefinition as DefaultConfigDefinition;
use Neunerlei\Configuration\State\ConfigState;
use TYPO3\CMS\Core\Site\Entity\Site;

class ConfigDefinition extends DefaultConfigDefinition implements NoDiInterface
{
    /**
     * @var \TYPO3\CMS\Core\Site\Entity\Site[]
     */
    protected $sites;
    
    /**
     * @var \LaborDigital\T3ba\ExtConfig\ExtConfigContext|\LaborDigital\T3ba\ExtConfig\SiteBased\SiteConfigContext
     */
    protected $configContext;
    
    /**
     * @inheritDoc
     */
    public function __construct(
        DefaultConfigDefinition $baseDefinition,
        array $sites
    )
    {
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
        $handler = $this->handlerDefinition->handler;
        
        if ($handler instanceof ExtendedSiteBasedHandlerInterface) {
            $handler->prepareSiteBasedConfig($this->configContext->getState());
        }
        
        foreach ($this->sites as $identifier => $site) {
            $this->runWithSiteBasedDefinition(
                $identifier, $site,
                function () {
                    parent::process();
                }
            );
        }
        
        if ($handler instanceof ExtendedSiteBasedHandlerInterface) {
            $handler->finishSiteBasedConfig($this->configContext->getState());
        }
        
        $this->configContext->getTypoContext()->di()->cs()->eventBus->dispatch(
            new SiteBasedExtConfigGeneratedEvent($this->configContext, $this->configContext->getState())
        );
    }
    
    /**
     * Internal helper to run the "process" method for a single site
     *
     * @param   string                            $identifier
     * @param   \TYPO3\CMS\Core\Site\Entity\Site  $site
     * @param   callable                          $callback
     *
     * @todo remove $identifier in v11, because we can extract identifier from $site directly
     */
    protected function runWithSiteBasedDefinition(string $identifier, Site $site, callable $callback): void
    {
        $clone = clone $this;
        
        if ($this->configContext instanceof SiteConfigContext) {
            $siteBackup = $this->configContext->getSite();
            $this->configContext->initializeSite($identifier, $site);
        }
        
        $state = $this->configContext->getState();
        $siteState = new ConfigState([]);
        $loaderContext = $this->configContext->getLoaderContext();
        $loaderConfigContextBackup = $loaderContext->configContext;
        $loaderContext->configContext = $this->configContext;
        $this->configContext->initialize($loaderContext, $siteState);
        
        $siteKeys = array_keys($this->sites);
        $siteConfigClasses = array_filter($this->configClasses, function ($v) use ($identifier, $siteKeys) {
            return $this->filterSiteConfigClass($identifier, $siteKeys, $v);
        });
        
        $filter = static function (array $value) use ($siteConfigClasses) {
            return array_filter($value, static function ($key) use ($siteConfigClasses) {
                return in_array($key, $siteConfigClasses, true);
            }, ARRAY_FILTER_USE_KEY);
        };
        
        $this->configClasses = $siteConfigClasses;
        $this->overrideConfigClasses = $filter($this->overrideConfigClasses);
        $this->classNamespaceMap = $filter($this->classNamespaceMap);
        
        try {
            $loaderContext->configContext = $this->configContext;
            $callback();
        } finally {
            // Revert the context back to the initial state
            $loaderContext->configContext = $loaderConfigContextBackup;
            $this->configContext->initialize($loaderContext, $state);
            $this->configClasses = $clone->configClasses;
            $this->overrideConfigClasses = $clone->overrideConfigClasses;
            $this->classNamespaceMap = $clone->classNamespaceMap;
            
            if (isset($siteBackup)) {
                $this->configContext->initializeSite($siteBackup->getIdentifier(), $siteBackup);
            }
        }
        
        $this->configContext->getTypoContext()->di()->cs()->eventBus->dispatch(
            new SingleSiteBasedExtConfigGeneratedEvent($identifier, $this->configContext, $siteState)
        );
        
        // Inject the site state into the main state object
        $data = $siteState->getAll();
        
        // Special handling for the "root" node -> this allows to configure non-site-based configuration
        $mergeOptions = $this->configContext->getExtConfigService()->getStateMergeOptions();
        if (isset($data['root']) && is_array($data['root'])) {
            $state->importFrom(new ConfigState($data['root']), $mergeOptions);
            unset($data['root']);
        }
        
        $state->importFrom(new ConfigState(['typo' => ['site' => [$identifier => $data]]]), $mergeOptions);
    }
    
    /**
     * Checks if a site config class can is applicable for a site called $identifier
     *
     * @param   string  $identifier  The identifier of the site to test the class for
     * @param   array   $siteKeys    The list of all available site identifiers
     * @param   string  $class       The name of the class to be filtered
     *
     * @return bool
     */
    protected function filterSiteConfigClass(string $identifier, array $siteKeys, string $class): bool
    {
        if (in_array(SiteIdentifierProviderInterface::class, class_implements($class), true)) {
            /** @var SiteIdentifierProviderInterface $class */
            $result = $class::getSiteIdentifiers($siteKeys);
            if (! empty($result) && ! in_array($identifier, $result, true)) {
                return false;
            }
        } elseif (in_array(SiteKeyProviderInterface::class, class_implements($class), true)) {
            // @todo remove this in v11
            // Extract sites using the site key provider
            /** @var SiteKeyProviderInterface $class */
            $result = $class::getSiteKeys($siteKeys);
            if (! empty($result) && ! in_array($identifier, $result, true)) {
                return false;
            }
        } elseif (str_contains($class, '\\Site\\')) {
            // Extract site based on namespace convention
            foreach ($this->handlerDefinition->locations as $handlerLocation) {
                $nsPattern = '\\' . trim(str_replace('/', '\\', $handlerLocation), '\\') . '\\Site\\';
                $nsPattern = preg_quote($nsPattern, '~') . '(.*?)\\\\';
                preg_match('~' . $nsPattern . '~', $class, $m);
                if (! empty($m) && ! empty($m[1])) {
                    $thisIdentifier = strtolower($m[1]);
                    if ($thisIdentifier !== 'common' && strtolower($identifier) !== $thisIdentifier) {
                        return false;
                    }
                }
            }
        }
        
        return true;
    }
    
}
