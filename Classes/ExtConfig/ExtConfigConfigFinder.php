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
 * Last modified: 2021.11.19 at 13:01
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfig;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\ExtConfig\Interfaces\SiteBasedHandlerInterface;
use LaborDigital\T3ba\ExtConfig\SiteBased\ConfigDefinition as SiteConfigDefinition;
use LaborDigital\T3ba\ExtConfig\SiteBased\SiteConfigContext;
use LaborDigital\T3ba\T3baFeatureToggles;
use Neunerlei\Configuration\Finder\ConfigFinder;
use Neunerlei\Configuration\Handler\HandlerDefinition;
use Neunerlei\Configuration\Loader\ConfigContext;
use Neunerlei\Configuration\Loader\ConfigDefinition;
use TYPO3\CMS\Core\Site\Entity\NullSite;

class ExtConfigConfigFinder extends ConfigFinder
{
    use ContainerAwareTrait;
    
    /**
     * @var array
     */
    protected $sites;
    
    /**
     * SiteBasedConfigFinder constructor.
     *
     * @param   array  $sites
     */
    public function __construct(array $sites)
    {
        $this->sites = $sites;
    }
    
    /**
     * @inheritDoc
     */
    public function find(HandlerDefinition $handlerDefinition, ConfigContext $configContext): ConfigDefinition
    {
        if (! $handlerDefinition->handler instanceof SiteBasedHandlerInterface) {
            return parent::find($handlerDefinition, $configContext);
        }
        
        /** @var \LaborDigital\T3ba\ExtConfig\ExtConfigContext $configContext */
        $useV11SiteBasedLoading
            = $configContext->getTypoContext()->config()->isFeatureEnabled(T3baFeatureToggles::EXT_CONFIG_V11_SITE_BASED_CONFIG);
        if (! $useV11SiteBasedLoading) {
            return new NullConfigDefinition($handlerDefinition, $configContext, [], [], []);
        }
        
        $siteConfigContext = $this->makeInstance(SiteConfigContext::class, [
            $configContext->getExtConfigService(),
            $configContext->getTypoContext(),
        ]);
        $siteConfigContext->initialize($configContext->getLoaderContext(), $configContext->getState());
        $siteConfigContext->initializeSite('null', new NullSite());
        
        return $this->makeInstance(
            SiteConfigDefinition::class,
            [
                parent::find($handlerDefinition, $siteConfigContext),
                $this->sites,
            ]
        );
    }
}