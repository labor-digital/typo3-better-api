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


namespace LaborDigital\T3ba\ExtConfigHandler\Frontend;


use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractSimpleExtConfigHandler;
use LaborDigital\T3ba\ExtConfig\Interfaces\ExtendedSiteBasedHandlerInterface;
use LaborDigital\T3ba\ExtConfig\Interfaces\SiteBasedHandlerInterface;
use LaborDigital\T3ba\ExtConfigHandler\TypoScript\Interop\TypoScriptConfigInteropLayer;
use LaborDigital\T3ba\ExtConfigHandler\TypoScript\TypoScriptConfigurator;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Neunerlei\Configuration\State\ConfigState;

class Handler extends AbstractSimpleExtConfigHandler implements SiteBasedHandlerInterface, ExtendedSiteBasedHandlerInterface
{
    protected $configureMethod = 'configureFrontend';
    
    /**
     * @var \LaborDigital\T3ba\ExtConfig\SiteBased\SiteConfigContext
     */
    protected $context;
    
    /**
     * @var \LaborDigital\T3ba\ExtConfigHandler\TypoScript\Interop\TypoScriptConfigInteropLayer
     */
    protected $tsInterop;
    
    /**
     * @var array
     * @deprecated temporary, fallback implementation until v11
     */
    protected $legacyTs = [];
    
    public function __construct(TypoScriptConfigInteropLayer $tsInterop)
    {
        $this->tsInterop = $tsInterop;
    }
    
    /**
     * @inheritDoc
     */
    protected function getConfiguratorClass(): string
    {
        return FrontendConfigurator::class;
    }
    
    /**
     * @inheritDoc
     */
    protected function getStateNamespace(): string
    {
        return 'frontend';
    }
    
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $this->registerDefaultLocation($configurator);
        $configurator->registerInterface(ConfigureFrontendInterface::class);
        $configurator->executeThisHandlerBefore(\LaborDigital\T3ba\ExtConfigHandler\TypoScript\Handler::class);
    }
    
    /**
     * @inheritDoc
     */
    public function finish(): void
    {
        parent::finish();
        
        if ($this->configurator instanceof FrontendConfigurator && $this->configurator->getFavIcon()) {
            $ts = '[betterSite("identifier") == "' . $this->context->getSiteIdentifier() . '"]' . PHP_EOL .
                  'page.shortcutIcon = ' . $this->configurator->getFavIcon() . PHP_EOL .
                  '[END]';
            
            // @todo this if can be removed in v11
            if ($this->tsInterop->isLocked()) {
                $this->legacyTs[] = $ts;
                
            } else {
                $this->tsInterop->registerConfiguration(
                    function (TypoScriptConfigurator $configurator) use ($ts) {
                        $configurator->registerDynamicContent('generic.setup', $ts);
                    },
                    't3ba.' . $this->getStateNamespace()
                );
            }
        }
    }
    
    /**
     * @inheritDoc
     * @deprecated temporary implementation until v11
     */
    public function prepareSiteBasedConfig(ConfigState $state): void { }
    
    /**
     * @inheritDoc
     * @deprecated temporary implementation until v11
     */
    public function finishSiteBasedConfig(ConfigState $state): void
    {
        if (empty($this->legacyTs)) {
            return;
        }
        
        $state
            ->useNamespace(
                'typo.typoScript.dynamicTypoScript',
                function (ConfigState $state) {
                    $state->attachToString('generic\\.setup', implode(PHP_EOL, $this->legacyTs), true);
                }
            );
    }
    
    
}