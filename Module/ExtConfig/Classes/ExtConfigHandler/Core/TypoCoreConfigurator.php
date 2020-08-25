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
 * Last modified: 2020.08.24 at 21:19
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig\ExtConfigHandler\Core;


use LaborDigital\T3BA\ExtConfig\ExtConfigContextAwareInterface;
use LaborDigital\T3BA\ExtConfig\ExtConfigContextAwareTrait;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\Options\Options;

class TypoCoreConfigurator implements ExtConfigContextAwareInterface
{
    use ExtConfigContextAwareTrait;

    /**
     * The list of registered x classes
     *
     * @var array
     */
    protected $xClasses = [];

    /**
     * The list of registered cache configurations
     *
     * @var array
     */
    protected $cacheConfigurations = [];

    /**
     * Registers a xClass override for a given class
     *
     * @param   string  $classToOverride      The class to override with the xClass
     * @param   string  $classToOverrideWith  The class to use as a xClass
     *
     * @return \LaborDigital\T3BA\ExtConfig\ExtConfigHandler\Core\TypoCoreConfigurator
     * @see https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/Xclasses/Index.html
     */
    public function registerXClass(string $classToOverride, string $classToOverrideWith): self
    {
        $this->xClasses[$classToOverride] = $classToOverrideWith;

        return $this;
    }

    /**
     * Registers a new cache configuration to typo3's caching framework.
     *
     * @param   string  $key       The cache key which is used to retrieve the cache instance later
     * @param   string  $frontend  The classname of the frontend to use
     * @param   string  $backend   The classname of the backend to use
     * @param   array   $options   Additional options for this cache
     *                             - options: (array) default: [] | Additional configuration for your backend.
     *                             Take a look a the typo3 documentation to see which options are supported.
     *                             - groups: (array|string) default: [] | One or multiple cache groups that should
     *                             be able to flush this cache. Allowed values are "all", "system" and "pages"
     *
     * @return \LaborDigital\T3BA\ExtConfig\ExtConfigHandler\Core\TypoCoreConfigurator
     *
     * @see https://stackoverflow.com/a/39446841
     * @see https://docs.typo3.org/typo3cms/CoreApiReference/latest/ApiOverview/CachingFramework/Developer/Index.html
     */
    public function registerCache(
        string $key,
        string $frontend,
        string $backend,
        array $options = []
    ): self {
        $options = Options::make($options,
            [
                'options' => [[]],
                'groups'  => [
                    'type'      => ['string', 'array'],
                    'filter'    => static function ($v) {
                        return is_array($v) ? $v : [$v];
                    },
                    'default'   => [],
                    'validator' => function ($v) {
                        if (! empty(array_filter($v, static function ($v) {
                            return in_array($v, ['all', 'system', 'pages'], true);
                        }))) {
                            return 'Your cache groups are invalid! Only the values all, system and pages are allowed!';
                        }

                        return true;
                    },
                ],
            ]);

        $this->cacheConfigurations[$this->context->replaceMarkers($key)] = [
            'frontend' => $frontend,
            'backend'  => $backend,
            'options'  => $this->context->replaceMarkers($options['options']),
            'groups'   => $this->context->replaceMarkers($options['groups']),
        ];

        return $this;
    }

    /**
     * Internal helper to store the configuration on the config state
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $state
     */
    public function finish(ConfigState $state): void
    {
        $state->set('xClass', $this->xClasses);
        $state->set('cacheConfiguration', $this->cacheConfigurations);
    }
}
