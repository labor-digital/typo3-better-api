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
 * Last modified: 2021.04.28 at 16:21
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Cache\KeyGenerator;


use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Event\Cache\EnvironmentCacheKeyArgFilterEvent;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use Neunerlei\Configuration\State\LocallyCachedStatePropertyTrait;
use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;

class EnvironmentCacheKeyGenerator implements CacheKeyGeneratorInterface
{
    use ContainerAwareTrait;
    use LocallyCachedStatePropertyTrait;

    /**
     * A list of all registered cache key enhancers
     *
     * @var \LaborDigital\T3BA\Tool\Cache\KeyGenerator\EnvironmentCacheKeyEnhancerInterface[]
     */
    protected $enhancers = [];

    /**
     * @var \LaborDigital\T3BA\Tool\TypoContext\TypoContext
     */
    protected $context;

    public function __construct(array $enhancers, TypoContext $context)
    {
        $this->enhancers = $enhancers;
        $this->context   = $context;
    }

    /**
     * @inheritDoc
     */
    public function makeCacheKey(): string
    {
        $args = $this->context->env()->isFrontend() ?
            $this->getFrontendArgs() : $this->getBackendArgs();

        foreach ($this->enhancers as $enhancer) {
            $args = $enhancer->enhanceArgs($args, $this->context);
        }

        $args = $this->cs()->eventBus->dispatch(
            new EnvironmentCacheKeyArgFilterEvent($args, $this->context)
        )->getArgs();

        ksort($args);

        return implode('|', $args);
    }

    /**
     * Returns the arguments for when executed in a frontend environment
     *
     * @return array
     */
    protected function getFrontendArgs(): array
    {
        $tsfe = $this->cs()->tsfe->getTsfe();

        return [
            'pageType'     => $tsfe->type,
            'mountPoint'   => $tsfe->MP,
            'feGroups'     => implode(',', $this->context->feUser()->getGroupIds()),
            'languageCode' => $this->context->language()->getCurrentFrontendLanguage()->getTwoLetterIsoCode(),
            'isBeUser'     => $this->context->beUser()->isLoggedIn(),
            'siteRootPid'  => $this->context->site()->getCurrent()->getRootPageId(),
        ];
    }

    /**
     * Returns the arguments when executed in either the CLI or backend environment
     *
     * @return array|string[]
     */
    protected function getBackendArgs(): array
    {
        if ($this->context->beUser()->getUser() instanceof CommandLineUserAuthentication) {
            return ['CLI'];
        }

        return [
            'pageId' => $this->context->pid()->getCurrent(),
            'userId' => $this->context->beUser()->getUser()->user['uid'] ?? -1,
        ];
    }
}
