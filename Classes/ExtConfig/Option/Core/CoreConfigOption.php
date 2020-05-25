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
 * Last modified: 2020.03.18 at 19:36
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\Core;

use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption;
use Neunerlei\Options\Options;

/**
 * Class CoreConfigOption
 *
 * Can be used to configure TYPO3's core functionality
 *
 * @package LaborDigital\Typo3BetterApi\ExtConfig\Option\Core
 */
class CoreConfigOption extends AbstractExtConfigOption
{
    
    /**
     * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
     */
    protected $container;
    
    /**
     * CoreConfigOption constructor.
     *
     * @param \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface $container
     */
    public function __construct(TypoContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Helper to register a class implementation
     * @see https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/Xclasses/Index.html
     *
     * Generally you should avoid xClasses and use registerImplementation() instead!
     *
     * @param string $classToOverride
     * @param string $classToOverrideWith
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Core\CoreConfigOption
     */
    public function registerXClass(string $classToOverride, string $classToOverrideWith): CoreConfigOption
    {
        $this->container->setXClassFor($classToOverride, $classToOverrideWith);
        return $this;
    }
    
    /**
     * Registers a given interface for a given classname. So If the interface is required, the class can be resolved.
     * Note: This works for class overrides as well :)
     *
     * @param string $insteadOfThisClass The name of the class / interface you want to set the override /
     *                                   implementation of
     * @param string $useThisClass       The Class to implement / to override the other class with
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Core\CoreConfigOption
     */
    public function registerImplementation(string $insteadOfThisClass, string $useThisClass): CoreConfigOption
    {
        $this->container->setClassFor($insteadOfThisClass, $useThisClass);
        return $this;
    }
    
    /**
     * Registers a new cache configuration to typo3's caching framework.
     *
     * @param string $key      The cache key which is used to retrieve the cache instance later
     * @param string $frontend The classname of the frontend to use
     * @param string $backend  The classname of the backend to use
     * @param array  $options  Additional options for this cache
     *                         - options: (array) default: [] | Additional configuration for your backend.
     *                         Take a look a the typo3 documentation to see which options are supported.
     *                         - groups: (array|string) default: [] | One or multiple cache groups that should
     *                         be able to flush this cache. Allowed values are "all", "system" and "pages"
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Core\CoreConfigOption
     *
     * @see https://stackoverflow.com/a/39446841
     * @see https://docs.typo3.org/typo3cms/CoreApiReference/latest/ApiOverview/CachingFramework/Developer/Index.html
     */
    public function registerCache(string $key, string $frontend, string $backend, array $options = []): CoreConfigOption
    {
        $options = Options::make($options, [
            'options' => [[]],
            'groups'  => [
                'type'      => ['string', 'array'],
                'filter'    => function ($v) {
                    return is_array($v) ? $v : [$v];
                },
                'default'   => [],
                'validator' => function ($v) {
                    if (!empty(array_filter($v, function ($v) {
                        return $v !== 'all' && $v !== 'system' && $v !== 'pages';
                    }))) {
                        return 'Your cache groups are invalid! Only the values all, system and pages are allowed!';
                    }
                    return true;
                },
            ],
        ]);
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$this->replaceMarkers($key)] = [
            'frontend' => $frontend,
            'backend'  => $backend,
            'options'  => $this->replaceMarkers($options['options']),
            'groups'   => $this->replaceMarkers($options['groups']),
        ];
        return $this;
    }
}
