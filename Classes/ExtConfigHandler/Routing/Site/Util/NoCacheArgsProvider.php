<?php
/*
 * Copyright 2022 LABOR.digital
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
 * Last modified: 2022.04.23 at 11:32
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Routing\Site\Util;


use LaborDigital\T3ba\ExtConfig\Traits\SiteConfigAwareTrait;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;

class NoCacheArgsProvider implements SingletonInterface
{
    use SiteConfigAwareTrait;
    
    /**
     * @var \TYPO3\CMS\Frontend\Page\CacheHashCalculator
     */
    protected $hashCalculator;
    
    public function __construct(TypoContext $context, CacheHashCalculator $hashCalculator)
    {
        $this->context = $context;
        $this->hashCalculator = $hashCalculator;
        
        $this->registerConfig('routing.noCacheArgs');
    }
    
    /**
     * Applies the configured noCacheArgs either into the global or the given hash calculator instance
     *
     * @param   \TYPO3\CMS\Frontend\Page\CacheHashCalculator|null  $hashCalculator
     *
     * @return void
     */
    public function updateCHashCalculator(?CacheHashCalculator $hashCalculator = null): void
    {
        $args = $this->getNoCacheArgs();
        if (empty($args)) {
            return;
        }
        
        $config = $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash'] ?? [];
        $config['excludedParameters'] = array_unique(
            array_merge($config['excludedParameters'] ?? [], $args)
        );
        
        ($hashCalculator ?? $this->hashCalculator)->setConfiguration($config);
    }
    
    /**
     * Returns the list of configured noCacheArgs for the current site
     *
     * @return array
     */
    public function getNoCacheArgs(): array
    {
        return $this->getSiteConfig();
    }
}