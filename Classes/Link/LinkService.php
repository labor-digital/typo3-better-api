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

use Neunerlei\Options\Options;
use Neunerlei\PathUtil\Path;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class LinkService {
	/**
	 * @var \LaborDigital\Typo3BetterApi\Link\LinkContext|null
	 */
	protected $context;
	
	/**
	 * @var Request|null
	 */
	protected $controllerRequest;
	
	/**
	 * Holds the host name and protocol, once it was generated
	 * @var string|null
	 */
	protected $host;
	
	/**
	 * LinkService constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\Link\LinkContext $context
	 */
	public function __construct(LinkContext $context) {
		$this->context = $context;
	}
	
	/**
	 * Creates a new link instance which is a better version of the typo3 extbase query builder.
	 * You can use this method anywhere, no matter if you are in an extbase controller, the cli
	 * or somewhere in a hook you can always create links. For that we forcefully instantiate
	 * the typo3 frontend if required.
	 *
	 * @param string|null   $linkSet      Defines the link set which was previously defined in typoscript,
	 *                                    or using the LinkSetRepository in your php code. The set will
	 *                                    automatically be applied to the new link instance
	 * @param iterable|null $args         If you have a linkSet specified you can use this parameter to supply
	 *                                    additional arguments to the created link instance directly
	 * @param iterable|null $fragmentArgs If you have a linkSet specified you can use this parameter to supply
	 *                                    arguments to your fragment of the created link instance directly
	 *
	 * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
	 */
	public function getLink(?string $linkSet = NULL, ?iterable $args = [], ?iterable $fragmentArgs = []): TypoLink {
		// Lazy load the context only when we need it
		$link = new TypoLink($this->context, $this->controllerRequest);
		
		// Inject link set and args if given
		if (!empty($linkSet)) $link = $link->withSetApplied($linkSet);
		if (!empty($args)) foreach ($args as $k => $v) $link = $link->withAddedToArgs($k, $v);
		if (!empty($fragmentArgs)) foreach ($fragmentArgs as $k => $v) $link = $link->withAddedToFragment($k, $v);
		
		// Done
		return $link;
	}
	
	/**
	 * This helper can be used to render typo3's text urls which look like t3://page?uid=26
	 * into a real, url using the typoscript cObject of the frontend
	 *
	 * @param string|array $typoLink Can by either a textual representation, like t3://page?uid=26
	 *                               or a full blown typoscript config array which will be rendered.
	 *
	 * @return string
	 */
	public function getTypoLink($typoLink): string {
		return $this->context->getContentObject()->typoLink_URL(
			is_string($typoLink) ? ["parameter" => $typoLink, "forceAbsoluteUrl" => 1] : $typoLink
		);
	}
	
	/**
	 * Returns the target frame for a typo link definition object.
	 *
	 * @param string|array $typoLink Can by either a textual representation, like t3://page?uid=26
	 *                               or a full blown typoscript config array which will be rendered.
	 *
	 * @return string
	 */
	public function getTypoLinkTarget($typoLink): string {
		$cObj = $this->context->getContentObject();
		$this->getTypoLink($typoLink);
		return empty($cObj->lastTypoLinkTarget) ? "_self" : $cObj->lastTypoLinkTarget;
	}
	
	/**
	 * This helper can be used to render a typo3 backend url.
	 * There are currently TWO possible options of creating links.
	 * 1. Creating a link by a module. Modules look like "web_list", "web_ts"...
	 * 2. Creating a link by a route. Routes are registered in the backend router.
	 *
	 * This method will take the $target and first check if it matches a route,
	 * if so it will generate the url for that route. If it does not match the url of a route
	 * it will automatically generate the url for the respective module instead.
	 *
	 * @param string $target  Either the route or the module identifier to build the url for
	 * @param array  $options Additional config options
	 *                        - mode string (auto): By default the type of link to create is selected
	 *                        automatically (routes get priority over modules). If you want to specify
	 *                        which type of link we should generate, you may set this to either "module" or "route".
	 *                        - args array: Additional parameter that should be passed on by the link
	 *
	 * @return string
	 */
	public function getBackendLink(string $target, array $options = []): string {
		// Skip if we are not in the backend
		if (!$this->context->TypoContext->getEnvAspect()->isBackend()) return "";
		
		// Prepare options
		$options = Options::make($options, [
			"mode" => [
				"type"    => "string",
				"values"  => ["auto", "module", "route"],
				"default" => "auto",
			],
			"args" => [
				"type"    => "array",
				"default" => [],
			],
		]);
		
		// Load the existing routes
		if ($options["mode"] === "auto") {
			$routes = $this->getBackendRoutes();
			if (isset($routes[$target])) $options["mode"] = "route";
			else $options["mode"] = "module";
		}
		
		// Build the uri
		if ($options["mode"] === "route")
			$uri = $this->context->BackendUriBuilder
				->buildUriFromRoute($target, $options["args"], \TYPO3\CMS\Backend\Routing\UriBuilder::ABSOLUTE_URL);
		else
			$uri = $this->context->BackendUriBuilder
				->buildUriFromModule($target, $options["args"], \TYPO3\CMS\Backend\Routing\UriBuilder::ABSOLUTE_URL);
		
		// Done
		return (string)$uri;
	}
	
	/**
	 * Returns the list of all registered backend routes
	 * @return array
	 */
	public function getBackendRoutes(): array {
		// Skip if we are not in the backend
		if (!$this->context->TypoContext->getEnvAspect()->isBackend()) return [];
		
		// Load the routes from the router
		return $this->context->Router->getRoutes();
	}
	
	/**
	 * Returns the host name for the current request.
	 *
	 * Note to self: This will probably make issues if there is a multi-domain setup in typo3...
	 *
	 * @param bool $withProtocol
	 *
	 * @return string
	 */
	public function getHost(bool $withProtocol = TRUE): string {
		if (!empty($this->host)) return $this->host;
		$pid = $this->context->TypoContext->getPidAspect()->getCurrentPid();
		$rootLine = $this->context->Page->getRootLine($pid, TRUE);
		$rootUid = reset($rootLine);
		
		// Make sure we have a request object
		if (!is_null($this->context->TypoContext->getRequestAspect()->getRootRequest()))
			$domain = $this->getUriBuilder()->reset()->setTargetPageUid(!empty($rootUid) ? $rootUid["uid"] : 0)
				->setAddQueryString(FALSE)
				->setCreateAbsoluteUri(TRUE)
				->buildFrontendUri();
		if (empty($domain)) {
			if ($this->context->TypoContext->getSiteAspect()->hasSite())
				$uri = $this->context->TypoContext->getSiteAspect()->getSite()->getBase();
			else $uri = Path::makeUri(TRUE);
			$domain = $uri->getScheme() . "://" . $uri->getHost();
		}
		$domain = parse_url($domain);
		return $this->host = ($withProtocol ? ($domain["scheme"] . "://") : "") . $domain["host"];
	}
	
	/**
	 * Can be used to retrieve the fully qualified url of a given file object
	 *
	 * @param $file
	 *
	 * @return string
	 */
	public function getFileLink($file): string {
		return $this->context->FalFiles->getFileInfo($file)->getUrl();
	}
	
	/**
	 * Returns a instance of the default extbase uri builder
	 *
	 * @return \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
	 */
	public function getUriBuilder(): UriBuilder {
		return $this->context->getUriBuilder();
	}
	
	/**
	 * Internal helper which is called in the BetterActionController to automatically
	 * inject the controller's request object into the TypoLink instance when it is created
	 *
	 * @param \TYPO3\CMS\Extbase\Mvc\RequestInterface $request
	 *
	 * @internal
	 *
	 */
	public function __setControllerRequest(RequestInterface $request) {
		$this->controllerRequest = $request;
	}
}