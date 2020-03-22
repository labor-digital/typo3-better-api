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
 * Last modified: 2020.03.19 at 01:43
 */

namespace LaborDigital\Typo3BetterApi\Link;

use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
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
 * @package LaborDigital\Typo3BetterApi\Links
 *
 * @property Router                                $Router
 * @property ExtensionService                      $Extension
 * @property LinkSetRepository                     $LinkSetRepo
 * @property \TYPO3\CMS\Backend\Routing\UriBuilder $BackendUriBuilder
 */
class LinkContext implements SingletonInterface {
	use CommonServiceLocatorTrait;
	
	/**
	 * True when we initialized both the request and are sure the typoScript frontend is initialized correctly
	 * @var bool
	 */
	protected $initialized = FALSE;
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\Link\ExtendedUriBuilder
	 */
	protected $uriBuilder;
	
	/**
	 * @var Request
	 */
	protected $request;
	
	/**
	 * @var ContentObjectRenderer
	 */
	protected $contentObject;
	
	/**
	 * LinkContext constructor.
	 */
	public function __construct() {
		$this->addToServiceMap([
			"Router"            => Router::class,
			"Extension"         => ExtensionService::class,
			"LinkSetRepo"       => LinkSetRepository::class,
			"BackendUriBuilder" => \TYPO3\CMS\Backend\Routing\UriBuilder::class,
		]);
	}
	
	
	/**
	 * Returns the basic request object
	 *
	 * @return \TYPO3\CMS\Extbase\Mvc\Request
	 */
	public function getRequest(): Request {
		$this->initializeIfRequired();
		return $this->request;
	}
	
	/**
	 * Returns the content object renderer and makes sure it is configured correctly, even if not in frontend context
	 *
	 * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	public function getContentObject(): ContentObjectRenderer {
		if (!empty($this->contentObject)) return $this->contentObject;
		$this->initializeIfRequired();
		return $this->contentObject = $this->Tsfe->getContentObjectRenderer();
	}
	
	/**
	 * Returns the instance of the typo3 extBase uri builder.
	 *
	 * This makes sure that all dependencies are met for the uri builder to work correctly.
	 *
	 * @return \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
	 */
	public function getUriBuilder(): UriBuilder {
		if (!empty($this->uriBuilder)) return $this->uriBuilder;
		$this->initializeIfRequired();
		
		// Create the uri builder
		$this->uriBuilder = $this->getInstanceOf(ExtendedUriBuilder::class);
		
		// Make sure we have a content object
		if (!$this->uriBuilder->hasContentObject()) {
			$this->uriBuilder->setContentObject($this->getContentObject());
		} else {
			$this->contentObject = $this->uriBuilder->getContentObject();
		}
		
		// Done
		return $this->uriBuilder;
	}
	
	/**
	 * Internal helper which is used to initialize the typoScript frontend controller
	 * if it is not existing, yet. This is needed for the uri builder to function correctly.
	 *
	 * It will also apply the configured base url to the http host in a cli environment.
	 */
	protected function initializeIfRequired() {
		if ($this->initialized) return;
		$this->initialized = TRUE;
		
		// Read the base url
		$baseUrl = Path::makeUri(rtrim($this->TypoScript->get("config.baseURL"), "/"))->getHost();
		
		// Create a new request instance
		$this->request = $this->getInstanceOf(\TYPO3\CMS\Extbase\Mvc\Web\Request::class);
		$this->request->setBaseUri($baseUrl);
		
		// Inject the base url if we are in cli context
		if ($this->TypoContext->getEnvAspect()->isCli()) {
			
			// Fix for external cli tools
			$_SERVER["SCRIPT_NAME"] = "/index.php";
			$_SERVER["SCRIPT_FILENAME"] = "/index.php";
			
			// Update typo3 internal caches
			$_SERVER['HTTP_HOST'] = $baseUrl;
			GeneralUtility::flushInternalRuntimeCaches();
		}
	}
}