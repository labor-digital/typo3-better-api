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
 * Last modified: 2021.05.02 at 19:11
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Routing\Site;


use LaborDigital\T3ba\ExtConfig\SiteBased\SiteConfigContext;

interface ConfigureSiteRoutingInterface
{
    
    /**
     * Allows you to configure the site aware routing options. Currently, this is mostly the configuration
     * of route enhancers on a specific side in your sites.yml
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\Routing\Site\SiteRoutingConfigurator  $configurator
     * @param   \LaborDigital\T3ba\ExtConfig\SiteBased\SiteConfigContext                  $context
     *
     * @see ConfigureRoutingInterface to configure global routing settings, like middlewares or
     *                                routing enhancer implementations.
     */
    public static function configureSiteRouting(SiteRoutingConfigurator $configurator, SiteConfigContext $context): void;
    
}
