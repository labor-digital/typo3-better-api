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


namespace LaborDigital\T3ba\ExtConfig\Traits;


use Neunerlei\Configuration\State\LocallyCachedStatePropertyTrait;

trait SiteConfigAwareTrait
{
    use LocallyCachedStatePropertyTrait;
    
    /**
     * The locally cached configuration
     *
     * @var array
     */
    protected $locallyCachedConfig;
    
    /**
     * @var \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     */
    protected $context;
    
    /**
     * Internal helper to be called in the __construct method.
     * It automatically hooks up the given config path to be synced with the local $config property
     * $this->context MUST be populated before using this
     *
     * @param   string         $configPath
     * @param   callable|null  $callback  Optional callback to execute when the config was changed
     */
    protected function registerConfig(string $configPath, ?callable $callback = null): void
    {
        $this->registerCachedProperty(
            'locallyCachedConfig',
            'typo.site.*.' . $configPath,
            $this->context->config()->getConfigState(),
            $callback !== null ? static function ($v) use ($callback) {
                return $callback($v) ?? $v;
            } : null,
            []
        );
    }
    
    /**
     * Returns the identifier of the currently active site
     *
     * @return string
     */
    protected function getSiteIdentifier(): string
    {
        // @todo we could make it so, that we listen on the SiteActivatedEvent
        // if it turns out to be reliable in all cases
        return $this->context->site()->getCurrent()->getIdentifier();
    }
    
    /**
     * Returns the configuration for the currently active TYPO3 site
     *
     * @param   string|null  $siteIdentifier  Optional site identifier to retrieve the config for a specific
     *                                        site instead of the current one
     *
     * @return array
     */
    protected function getSiteConfig(?string $siteIdentifier = null): array
    {
        return $this->locallyCachedConfig[$siteIdentifier ?? $this->getSiteIdentifier()] ?? [];
    }
}