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

use LaborDigital\T3BA\Core\Di\PublicServiceInterface;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class LinkService implements SingletonInterface, PublicServiceInterface
{
    /**
     * @var \LaborDigital\T3BA\Tool\Link\LinkContext
     */
    protected $context;

    /**
     * If used inside a better action controller this will hold the controller's request object
     *
     * @var Request|null
     */
    protected $controllerRequest;

    /**
     * Holds the host name and protocol, once it was generated
     *
     * @var string|null
     */
    protected $host;

    /**
     * LinkService constructor.
     *
     * @param   \LaborDigital\T3BA\Tool\Link\LinkContext  $context
     */
    public function __construct(LinkContext $context)
    {
        $this->context = $context;
    }

    /**
     * Returns true if a link definition with the given key exists.
     * Definitions can be configured using the ConfigureLinksInterface
     *
     * @param   string  $key  The key/name of the link definition to check for
     *
     * @return bool
     * @see \LaborDigital\T3BA\ExtConfigHandler\Link\ConfigureLinksInterface
     */
    public function hasDefinition(string $key): bool
    {
        return $this->context->hasDefinition($key);
    }

    /**
     * Creates a new link instance which is a better version of the typo3 extbase query builder.
     * You can use this method anywhere, no matter if you are in an extbase controller, the cli
     * or somewhere in a hook you can always create links. For that we forcefully instantiate
     * the typo3 frontend if required.
     *
     * @param   string|null    $definition    Allows you to provide the key of a link definition, which was
     *                                        configured using the ConfigureLinksInterface. The definition will
     *                                        automatically be applied to the new link instance
     * @param   iterable|null  $args          If you have a definition specified, you can use this parameter to supply
     *                                        additional arguments to the created link instance directly
     * @param   iterable|null  $fragmentArgs  If you have a definition specified, you can use this parameter to supply
     *                                        arguments to your fragment of the created link instance directly
     *
     * @return \LaborDigital\T3BA\Tool\Link\Link
     */
    public function getLink(?string $definition = null, ?iterable $args = [], ?iterable $fragmentArgs = []): Link
    {
        $link = GeneralUtility::makeInstance(Link::class, $this->context, $this->controllerRequest);

        // Inject link set and args if given
        if (! empty($definition)) {
            $link = $link->withSetApplied($definition);
        }
        if (! empty($args)) {
            foreach ($args as $k => $v) {
                $link = $link->withAddedToArgs($k, $v);
            }
        }
        if (! empty($fragmentArgs)) {
            foreach ($fragmentArgs as $k => $v) {
                $link = $link->withAddedToFragment($k, $v);
            }
        }

        // Done
        return $link;
    }

    /**
     * This helper can be used to render typo3's text urls which look like t3://page?uid=26
     * into a real, url using the typoScript cObject of the frontend
     *
     * @param   string|array  $typoLink  Can by either a textual representation, like t3://page?uid=26
     *                                   or a full blown typoScript config array which will be rendered.
     *
     * @return string
     */
    public function getTypoLink($typoLink): string
    {
        return $this->context->getContentObject()->typoLink_URL(
            is_string($typoLink) ? ['parameter' => $typoLink, 'forceAbsoluteUrl' => 1] : $typoLink
        );
    }

    /**
     * Returns the target frame for a typo link definition object.
     *
     * @param   string|array  $typoLink  Can by either a textual representation, like t3://page?uid=26
     *                                   or a full blown typoScript config array which will be rendered.
     *
     * @return string
     */
    public function getTypoLinkTarget($typoLink): string
    {
        $cObj = $this->context->getContentObject();
        $this->getTypoLink($typoLink);

        return empty($cObj->lastTypoLinkTarget) ? '_self' : $cObj->lastTypoLinkTarget;
    }

    /**
     * This helper to creating a link for a route. Routes are registered in the backend router.
     *
     * This method will take the $target and first check if it matches a route,
     * if so it will generate the url for that route. If it does not match the url of a route
     * it will automatically generate the url for the respective module instead.
     *
     * @param   string  $target   Either the route or the module identifier to build the url for
     * @param   array   $options  Additional config options
     *                            - args array: Additional parameter that should be passed on by the link
     *
     * @return string
     */
    public function getBackendLink(string $target, array $options = []): string
    {
        // Skip if we are not in the backend
        if (! $this->context->getTypoContext()->env()->isBackend()) {
            return '';
        }

        // Prepare options
        $options = Options::make($options, [
            'args' => [
                'type'    => 'array',
                'default' => [],
            ],
        ]);

        return (string)$this->context
            ->getBackendUriBuilder()
            ->buildUriFromRoute(
                $target,
                $options['args'],
                \TYPO3\CMS\Backend\Routing\UriBuilder::ABSOLUTE_URL
            );
    }

    /**
     * Returns the list of all registered backend routes
     *
     * @return array
     */
    public function getBackendRoutes(): array
    {
        // Skip if we are not in the backend
        if (! $this->context->getTypoContext()->env()->isBackend()) {
            return [];
        }

        // Load the routes from the router
        return $this->context->getBackendRouter()->getRoutes();
    }

    /**
     * Returns the host name for the current request.
     *
     * @param   bool  $withProtocol  True to add the default http or https protocol to the front of the host name
     *
     * @return string
     * @see        \LaborDigital\T3BA\Tool\TypoContext\Facet\RequestFacet::getHost()
     * @deprecated will be removed in v11
     */
    public function getHost(bool $withProtocol = true): string
    {
        return $this->context->getTypoContext()->request()->getHost($withProtocol);
    }

    /**
     * Can be used to retrieve the fully qualified url of a given file object
     *
     * @param   mixed  $file
     *
     * @return string
     */
    public function getFileLink($file): string
    {
        return $this->context->getFalService()->getFileUrl($file);
    }

    /**
     * Returns a instance of the default extbase uri builder
     *
     * @return \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
     */
    public function getUriBuilder(): UriBuilder
    {
        return $this->context->getUriBuilder();
    }

    /**
     * Internal helper to create a clone of this service for an extbase controller
     * that also holds the request object of the current controller.
     * This is used inside the BetterActionController.
     *
     * @param   \TYPO3\CMS\Extbase\Mvc\RequestInterface  $request
     *
     * @return $this
     */
    public function makeControllerClone(RequestInterface $request): self
    {
        $clone                    = clone $this;
        $clone->controllerRequest = $request;

        return $clone;
    }
}
