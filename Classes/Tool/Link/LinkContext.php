<?php
declare(strict_types=1);
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
 * Last modified: 2020.03.19 at 01:43
 */

namespace LaborDigital\T3BA\Tool\Link;

use LaborDigital\T3BA\Core\DependencyInjection\ContainerAwareTrait;
use LaborDigital\T3BA\Tool\Link\Adapter\ExtendedUriBuilder;
use LaborDigital\T3BA\Tool\Tsfe\TsfeService;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use Neunerlei\Configuration\State\LocallyCachedStatePropertyTrait;
use Neunerlei\PathUtil\Path;
use TYPO3\CMS\Backend\Routing\Router;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class LinkContext
 *
 * @package LaborDigital\Typo3BetterApi\Links
 */
class LinkContext implements SingletonInterface
{
    use ContainerAwareTrait;
    use LocallyCachedStatePropertyTrait;

    /**
     * True when we initialized both the request and are sure the typoScript frontend is initialized correctly
     *
     * @var bool
     */
    protected $initialized = false;

    /**
     * @var \LaborDigital\T3BA\Tool\TypoContext\TypoContext
     */
    protected $typoContext;

    /**
     * The list of existing link sets
     *
     * @var array
     */
    protected $linkSets;

    /**
     * LinkContext constructor.
     *
     * @param   \LaborDigital\T3BA\Tool\TypoContext\TypoContext  $typoContext
     */
    public function __construct(TypoContext $typoContext)
    {
        $this->typoContext = $typoContext;
        $this->registerCachedProperty('linkSets', 't3ba.link.sets', $typoContext->Config()->getConfigState());
    }

    /**
     * Returns the instance of the typo context
     *
     * @return \LaborDigital\T3BA\Tool\TypoContext\TypoContext
     */
    public function TypoContext(): TypoContext
    {
        return $this->typoContext;
    }

    /**
     * Returns the basic ext base request object
     *
     * @return \TYPO3\CMS\Extbase\Mvc\Request
     */
    public function Request(): Request
    {
        $this->initializeIfRequired();

        return $this->getSingletonOf(Request::class);
    }

    /**
     * Returns the content object renderer and makes sure it is configured correctly, even if not in frontend context
     *
     * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public function ContentObject(): ContentObjectRenderer
    {
        if ($this->hasLocalSingleton(ContentObjectRenderer::class)) {
            return $this->getSingletonOf(ContentObjectRenderer::class);
        }

        $this->initializeIfRequired();
        $cObj = $this->getInstanceOf(TsfeService::class)->getContentObjectRenderer();
        $this->setLocalSingleton(ContentObjectRenderer::class, $cObj);

        return $cObj;
    }

    /**
     * Returns the instance of the TYPO3 extBase uri builder.
     *
     * This makes sure that all dependencies are met for the uri builder to work correctly.
     *
     * @return \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
     */
    public function UriBuilder(): UriBuilder
    {
        if ($this->hasLocalSingleton(UriBuilder::class)) {
            return $this->getSingletonOf(UriBuilder::class);
        }

        $this->initializeIfRequired();
        $builder = $this->getInstanceOf(ExtendedUriBuilder::class);
        $this->setLocalSingleton(UriBuilder::class, $builder);
        if (! $builder->hasContentObject()) {
            $builder->setContentObject($this->ContentObject());
        } else {
            $this->setLocalSingleton(ContentObjectRenderer::class, $builder->getContentObject());
        }

        return $builder;
    }

    /**
     * Returns the instance of the TYPO3 backend uri builder
     *
     * @return \TYPO3\CMS\Backend\Routing\UriBuilder
     */
    public function BackendUriBuilder(): \TYPO3\CMS\Backend\Routing\UriBuilder
    {
        return $this->getSingletonOf(\TYPO3\CMS\Backend\Routing\UriBuilder::class);
    }

    /**
     * Returns the instance of the TYPO3 backend router
     *
     * @return \TYPO3\CMS\Backend\Routing\Router
     */
    public function BackendRouter(): Router
    {
        return $this->getSingletonOf(Router::class);
    }

    /**
     * Returns the instance of the extbase extension service
     *
     * @return \TYPO3\CMS\Extbase\Service\ExtensionService
     */
    public function ExtensionService(): ExtensionService
    {
        return $this->getSingletonOf(ExtensionService::class);
    }

    public function hasLinkSet(string $key): bool
    {
        return isset($this->linkSets[$key]);
    }

    public function getLinkSet(string $key): LinkSetDefinition
    {
        dbge($this->linkSets[$key]);
    }

    /**
     * Internal helper which is used to initialize the typoScript frontend controller
     * if it is not existing, yet. This is needed for the uri builder to function correctly.
     *
     * It will also apply the configured base url to the http host in a cli environment.
     */
    protected function initializeIfRequired(): void
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        // Read the base url
        $baseUrl = $this->typoContext->Config()->getTypoScriptValue('config.baseURL');
        if ($baseUrl === null) {
            $baseUrl = $this->typoContext->Request()->getHost();
        }
        $baseUrl = Path::makeUri($baseUrl)->getHost();

        // Create a new request instance
        $this->request = $this->getInstanceOf(Request::class);
        $this->request->setBaseUri($baseUrl);

        // Inject the base url if we are in cli context
        if ($this->typoContext->Env()->isCli()) {
            // Fix for external cli tools
            $_SERVER['SCRIPT_NAME']     = '/index.php';
            $_SERVER['SCRIPT_FILENAME'] = '/index.php';

            // Update TYPO3 internal caches
            /** @noinspection HostnameSubstitutionInspection */
            $_SERVER['HTTP_HOST'] = $baseUrl;
            GeneralUtility::flushInternalRuntimeCaches();
        }
    }
}
