<?php
/*
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
 * Last modified: 2020.08.24 at 22:07
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Http;


use LaborDigital\T3BA\ExtConfig\ExtConfigContextAwareInterface;
use LaborDigital\T3BA\ExtConfig\ExtConfigContextAwareTrait;

class HttpConfigurator implements ExtConfigContextAwareInterface
{
    use ExtConfigContextAwareTrait;

    /**
     * The list of registered route enhancers
     *
     * @var array
     */
    protected $routeEnhancers = [];

    /**
     * Registers a new, raw route enhancer configuration.
     *
     * @param   string  $key     The unique key for this route enhancer
     * @param   array   $config  The is the equivalent of the yaml configuration you would put into your site.config
     *                           file
     *
     * @return \LaborDigital\T3BA\ExtConfigHandler\Http\HttpConfigurator
     * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.5/Feature-86365-RoutingEnhancersAndAspects.html
     */
    public function registerRouteEnhancer(string $key, array $config): self
    {
        $this->routeEnhancers['raw'][$this->context->replaceMarkers($key)]
            = $this->context->replaceMarkers($config);

        return $this;
    }
}
