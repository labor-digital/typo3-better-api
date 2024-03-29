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


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\ExtConfig\SiteBased\ConfigDefinition as SiteConfigDefinition;
use Neunerlei\Configuration\Finder\ConfigFinder as DefaultConfigFinder;
use Neunerlei\Configuration\Handler\HandlerDefinition;
use Neunerlei\Configuration\Loader\ConfigContext;
use Neunerlei\Configuration\Loader\ConfigDefinition;

/**
 * @deprecated will be removed without replacement in v11
 */
class ConfigFinder extends DefaultConfigFinder
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
        return $this->makeInstance(
            SiteConfigDefinition::class,
            [
                parent::find($handlerDefinition, $configContext),
                $this->sites,
            ]
        );
    }
    
}
