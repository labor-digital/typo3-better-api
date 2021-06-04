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
 * Last modified: 2021.06.04 at 16:28
 */

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

namespace LaborDigital\T3ba\Tool\Link;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Tool\Fal\FalService;
use LaborDigital\T3ba\Tool\Link\Adapter\ExtendedUriBuilder;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use LaborDigital\T3ba\Tool\Tsfe\TsfeService;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use Neunerlei\Configuration\State\LocallyCachedStatePropertyTrait;
use Neunerlei\PathUtil\Path;
use TYPO3\CMS\Backend\Routing\Router;
use TYPO3\CMS\Backend\Routing\UriBuilder as BackendUriBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

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
     * @var \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     */
    protected $typoContext;
    
    /**
     * The list of existing link sets
     *
     * @var array
     */
    protected $definitions;
    
    /**
     * LinkContext constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\TypoContext\TypoContext  $typoContext
     */
    public function __construct(TypoContext $typoContext)
    {
        $this->typoContext = $typoContext;
        $this->registerCachedProperty(
            'definitions',
            't3ba.link.definitions',
            $typoContext->config()->getConfigState());
    }
    
    /**
     * Returns the instance of the typo context
     *
     * @return \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     */
    public function getTypoContext(): TypoContext
    {
        return $this->typoContext;
    }
    
    /**
     * Returns the basic ext base request object
     *
     * @return \TYPO3\CMS\Extbase\Mvc\Request
     */
    public function getRequest(): Request
    {
        $this->initializeIfRequired();
        
        return $this->getService(Request::class);
    }
    
    /**
     * Returns the content object renderer and makes sure it is configured correctly, even if not in frontend context
     *
     * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public function getContentObject(): ContentObjectRenderer
    {
        if ($this->hasService(ContentObjectRenderer::class)) {
            return $this->getService(ContentObjectRenderer::class);
        }
        
        $this->initializeIfRequired();
        $cObj = $this->getService(TsfeService::class)->getContentObjectRenderer();
        $this->setService(ContentObjectRenderer::class, $cObj);
        
        return $cObj;
    }
    
    /**
     * Returns the instance of the TYPO3 extBase uri builder.
     *
     * This makes sure that all dependencies are met for the uri builder to work correctly.
     *
     * @return \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
     */
    public function getUriBuilder(): UriBuilder
    {
        if ($this->hasService(UriBuilder::class)) {
            return $this->getService(UriBuilder::class);
        }
        
        $this->initializeIfRequired();
        $builder = $this->getService(ExtendedUriBuilder::class);
        $this->setService(UriBuilder::class, $builder);
        
        if (! $builder->hasContentObject()) {
            $builder->setContentObject($this->getContentObject());
        } else {
            $this->setService(ContentObjectRenderer::class, $builder->getContentObject());
        }
        
        return $builder;
    }
    
    /**
     * Returns the instance of the TYPO3 backend uri builder
     *
     * @return BackendUriBuilder
     */
    public function getBackendUriBuilder(): BackendUriBuilder
    {
        return $this->getService(BackendUriBuilder::class);
    }
    
    /**
     * Returns the instance of the TYPO3 backend router
     *
     * @return \TYPO3\CMS\Backend\Routing\Router
     */
    public function getBackendRouter(): Router
    {
        return $this->getService(Router::class);
    }
    
    /**
     * Returns the instance of the FAL service to generate file urls with
     *
     * @return \LaborDigital\T3ba\Tool\Fal\FalService
     */
    public function getFalService(): FalService
    {
        return $this->getService(FalService::class);
    }
    
    /**
     * Returns the instance of the extbase extension service
     *
     * @return \TYPO3\CMS\Extbase\Service\ExtensionService
     */
    public function getExtensionService(): ExtensionService
    {
        return $this->getService(ExtensionService::class);
    }
    
    /**
     * Returns true if a link definition with the given key exists.
     * Definitions can be configured using the ConfigureLinksInterface
     *
     * @param   string  $key  The key/name of the link definition to check for
     *
     * @return bool
     * @see \LaborDigital\T3ba\ExtConfigHandler\Link\ConfigureLinksInterface
     */
    public function hasDefinition(string $key): bool
    {
        return isset($this->definitions[$key]);
    }
    
    /**
     * Returns the configuration for a certain link definition if it exists.
     * Definitions can be configured using the ConfigureLinksInterface
     *
     * @param   string  $key  The key/name of the link definition to retrieve
     *
     * @return \LaborDigital\T3ba\Tool\Link\Definition
     * @throws \LaborDigital\T3ba\Tool\Link\DefinitionNotFoundException
     * @see \LaborDigital\T3ba\ExtConfigHandler\Link\ConfigureLinksInterface
     */
    public function getDefinitions(string $key): Definition
    {
        if (! isset($this->definitions[$key])) {
            throw new DefinitionNotFoundException(
                'The requested link definition with key: "' . $key . '" was not found!');
        }
        
        return SerializerUtil::unserialize($this->definitions[$key], [Definition::class]);
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
        $baseUrl = $this->typoContext->config()->getTypoScriptValue('config.baseURL');
        if ($baseUrl === null) {
            $baseUrl = $this->typoContext->request()->getHost();
        }
        $baseUrl = Path::makeUri($baseUrl)->getHost();
        
        // Create a new request instance
        $request = $this->makeInstance(Request::class);
        $request->setBaseUri($baseUrl);
        $this->setService(Request::class, $request);
        
        // Inject the base url if we are in cli context
        if ($this->typoContext->env()->isCli()) {
            // Fix for external cli tools
            $_SERVER['SCRIPT_NAME'] = '/index.php';
            $_SERVER['SCRIPT_FILENAME'] = '/index.php';
            
            // Update TYPO3 internal caches
            /** @noinspection HostnameSubstitutionInspection */
            $_SERVER['HTTP_HOST'] = $baseUrl;
            GeneralUtility::flushInternalRuntimeCaches();
        }
    }
}
