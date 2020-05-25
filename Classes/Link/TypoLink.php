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
 * Last modified: 2020.03.19 at 01:45
 */

namespace LaborDigital\Typo3BetterApi\Link;

use Neunerlei\Options\Options;
use Neunerlei\PathUtil\Path;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use function GuzzleHttp\Psr7\parse_query;

/**
 * Class TypoLink
 *
 * Please note that this class is immutable!
 *
 * @package LaborDigital\Typo3BetterApi\Links
 */
class TypoLink
{
    
    /**
     * @var \LaborDigital\Typo3BetterApi\Link\LinkContext
     */
    protected $context;
    
    /**
     * @var Request|null
     */
    protected $controllerRequest;
    
    /**
     * The target page id
     * @var int
     */
    protected $pid;
    
    /**
     * True if the current query string should be appended to the new url
     * @var bool
     */
    protected $keepQuery = false;
    
    /**
     * If $keepQuery is set to TRUE. This list defines which query parameters should be kept.
     * All others will be dropped.
     * NOTE: $queryBlacklist has priority over the whitelist. You can not use both together!
     * @var array
     */
    protected $allowedQueryArgs = [];
    
    /**
     * If $keepQuery is set to TRUE. This list defines which query parameters should be removed.
     * All others will be kept.
     * NOTE: $queryBlacklist has priority over the whitelist. You can not use both together!
     * @var array
     */
    protected $deniedQueryArgs = [];
    
    /**
     * The fragment / hash / anchor of the url
     * @var string|iterable|null
     */
    protected $fragment;
    
    /**
     * Optional The controller class to create the request for
     * @var string
     */
    protected $controllerClass;
    
    /**
     * Optional if the controller class name is not known.
     * NOTE: $controllerClass has priority over this setting
     * @var string
     */
    protected $controllerName;
    
    /**
     * Optional if the controller class name is not known.
     * NOTE: $controllerClass has priority over this setting
     * @var string
     */
    protected $controllerExtKey;
    
    /**
     * Optional The controller action to create the link for
     * @var string
     */
    protected $controllerAction;
    
    /**
     * Optional The plugin name to create the link for
     * @var string
     */
    protected $pluginName;
    
    /**
     * The arguments to build the link with
     * @var array
     */
    protected $args = [];
    
    /**
     * True as long as the chash should be added to the generated link
     * @var bool
     */
    protected $cHash = true;
    
    /**
     * Holds the user defined request object
     * @var Request
     */
    protected $request;
    
    /**
     * Holds the user defined uri builder
     * @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
     */
    protected $uriBuilder;
    
    /**
     * Used if a link set was applied which requires specific arguments to be present
     * @var array
     */
    protected $requiredArgs = [];
    
    /**
     * Used if a link set was applied which requires specific fragments-arguments to be present
     * @var array
     */
    protected $requiredFragmentArgs = [];
    
    /**
     * Holds the language this link should be generated for
     * @var \TYPO3\CMS\Core\Site\Entity\SiteLanguage|null
     */
    protected $language;
    
    /**
     * Link constructor.
     *
     * @param \LaborDigital\Typo3BetterApi\Link\LinkContext $context
     * @param \TYPO3\CMS\Extbase\Mvc\Request|null           $controllerRequest
     */
    public function __construct(LinkContext $context, ?Request $controllerRequest)
    {
        $this->context = $context;
        $this->controllerRequest = $controllerRequest;
    }
    
    /**
     * Make sure the linked objects are cloned when we clone ourselves
     */
    public function __clone()
    {
        if (!empty($this->request)) {
            $this->request = clone $this->request;
        }
        if (!empty($this->uriBuilder)) {
            $this->uriBuilder = clone $this->uriBuilder;
        }
    }
    
    /**
     * Returns the target page id or null
     * @return int|null
     */
    public function getPid(): ?int
    {
        return $this->pid;
    }
    
    /**
     * Sets the target page id
     *
     * @param int|string $pid
     *
     * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
     */
    public function withPid($pid): TypoLink
    {
        $clone = clone $this;
        if (!is_numeric($pid)) {
            $pid = $this->context->TypoContext->getPidAspect()->getPid($pid);
        }
        $clone->pid = (int)$pid;
        return $clone;
    }
    
    /**
     * Returns the request which was set for this link.
     *
     * @param bool $alsoInternal Be careful with this param.
     *                           If your set this to true the method will return the internal request
     *                           which is stored in the global context of the used "LinkService" instance.
     *                           If you change stuff in it you may break stuff.
     *
     * @return \TYPO3\CMS\Extbase\Mvc\Web\Request|null
     */
    public function getRequest(bool $alsoInternal): ?Request
    {
        return !empty($this->request) ? $this->request : ($alsoInternal ? $this->context->getRequest() : null);
    }
    
    /**
     * Sets the request object of this link instance
     *
     * @param \TYPO3\CMS\Extbase\Mvc\Request $request
     *
     * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
     */
    public function withRequest(Request $request): TypoLink
    {
        $clone = clone $this;
        $clone->request = $request;
        return $clone;
    }
    
    /**
     * Returns the set uri builder for this link
     *
     * @param bool $alsoInternal Be careful with this param.
     *                           If your set this to true the method will return the internal uri builder
     *                           which is stored in the global context of the used "LinkService" instance.
     *                           If you change stuff in it, you may break in other places.
     *
     * @return \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
     */
    public function getUriBuilder(bool $alsoInternal): UriBuilder
    {
        return !empty($this->uriBuilder) ? $this->uriBuilder : ($alsoInternal ? $this->context->getUriBuilder() : null);
    }
    
    /**
     * Sets the uri builder of this link instance
     *
     * @param \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder
     *
     * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
     */
    public function withUriBuilder(UriBuilder $uriBuilder): TypoLink
    {
        $clone = clone $this;
        $clone->uriBuilder = $uriBuilder;
        return $clone;
    }
    
    /**
     * Returns true if the current query string should be kept, otherwise false
     *
     * @return bool
     */
    public function getKeepQuery(): bool
    {
        return $this->keepQuery;
    }
    
    /**
     * Setting this to true will keep the current query string.
     * Default is false.
     *
     * @param bool $keepQuery
     *
     * @return TypoLink
     */
    public function withKeepQuery(bool $keepQuery): TypoLink
    {
        $clone = clone $this;
        $clone->keepQuery = $keepQuery;
        return $clone;
    }
    
    /**
     * Returns an array containing two keys:
     * - "type" is either denied, allowed or none which represents the type of query modifier that is used
     * - "list" is an array of all set configuration for the given type
     *
     * @return array
     */
    public function getQueryModifiers(): array
    {
        if (!empty($this->deniedQueryArgs)) {
            return [
                'type' => 'denied',
                'list' => $this->deniedQueryArgs,
            ];
        } elseif (!empty($this->allowedQueryArgs)) {
            return [
                'type' => 'allowed',
                'list' => $this->deniedQueryArgs,
            ];
        }
        return [
            'type' => 'none',
            'list' => [],
        ];
    }
    
    /**
     * If $keepQuery is set to TRUE. This list defines which query parameters should be kept.
     * All others will be dropped.
     * NOTE: $queryBlacklist has priority over the whitelist. You can not use both together!
     *
     * @param array $list
     *
     * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
     */
    public function withAllowedQueryArgs(array $list): TypoLink
    {
        $clone = clone $this;
        $clone->allowedQueryArgs = array_values($list);
        return $clone;
    }
    
    /**
     * If $keepQuery is set to TRUE. This list defines which query parameters should be removed.
     * All others will be kept.
     * NOTE: $queryBlacklist has priority over the whitelist. You can not use both together!
     *
     * @param array $list
     *
     * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
     */
    public function withDeniedQueryArgs(array $list): TypoLink
    {
        $clone = clone $this;
        $clone->deniedQueryArgs = array_values($list);
        return $clone;
    }
    
    /**
     * Returns the fragment/anchor tag of the link
     *
     * Can be either a string like: myFragment
     * or an iterable object like myKey => myValue, keyB => valueB ...
     *
     * @return string|null|array
     */
    public function getFragment()
    {
        return $this->fragment;
    }
    
    /**
     * Sets the fragment/anchor tag of the link
     *
     * Can be either a string like: myFragment
     * or an iterable object like myKey => myValue, keyB => valueB ...
     * The latter version will then be converted into #myKey/myValue/keyB/valueB
     *
     * @param string|null|array $fragment
     *
     * @return TypoLink
     * @throws \LaborDigital\Typo3BetterApi\Link\LinkException
     */
    public function withFragment($fragment): TypoLink
    {
        $clone = clone $this;
        if (!is_array($fragment) && !is_string($fragment) && !is_null($fragment)) {
            throw new LinkException('The given fragment is invalid!');
        }
        $clone->fragment = is_string($fragment) ? trim(ltrim(trim($fragment), '#')) : $fragment;
        return $clone;
    }
    
    /**
     * Adds a single fragment argument and its value to the link
     *
     * @param string $key   The key to set the value for
     * @param mixed  $value The value to set for the given key
     *
     * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
     * @throws \LaborDigital\Typo3BetterApi\Link\LinkException
     */
    public function withAddedToFragment(string $key, $value): TypoLink
    {
        $clone = clone $this;
        if (!is_iterable($clone->fragment)) {
            if (!empty($clone->fragment)) {
                throw new LinkException('Can not add key: ' . $key . ' to the link\'s fragment, because the fragment is currently not iterable!');
            }
            $clone->fragment = [];
        }
        $clone->fragment[trim(ltrim(trim($key), '#'))] = $value;
        return $clone;
    }
    
    /**
     * Removes a single argument from the list of fragment arguments
     *
     * @param string $key
     *
     * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
     */
    public function withRemovedFromFragment(string $key): TypoLink
    {
        $clone = clone $this;
        if (!is_iterable($clone->fragment)) {
            return $clone;
        }
        unset($clone->args[trim(ltrim(trim($key), '#'))]);
        return $clone;
    }
    
    /**
     * Returns the currently configured extbase controller target-class for the link
     * @return string
     */
    public function getControllerClass(): string
    {
        return $this->controllerClass;
    }
    
    /**
     * Can be used to set the target extbase controller, extension and vendor for this link.
     *
     * @param string $controllerClass
     *
     * @return TypoLink
     */
    public function withControllerClass(string $controllerClass): TypoLink
    {
        $clone = clone $this;
        $clone->controllerClass = $controllerClass;
        return $clone;
    }
    
    /**
     * Returns the currently set extbase controller name for this link.
     * Optional if the controller class name is not known.
     * NOTE: $controllerClass has priority over this setting
     * NOTE 2: if $controllerClass is set, this will NOT return the controller name for that setting!
     * @return string
     */
    public function getControllerName(): string
    {
        return $this->controllerName;
    }
    
    /**
     * Sets the used extbase controller name for this link.
     * Optional if the controller class name is not known.
     * NOTE: $controllerClass has priority over this setting
     *
     * @param string $controllerName
     *
     * @return TypoLink
     */
    public function withControllerName(string $controllerName): TypoLink
    {
        $clone = clone $this;
        $clone->controllerName = $controllerName;
        return $clone;
    }
    
    /**
     * Returns the currently set extbase extension key for the controller used by this link.
     * Optional if the controller class name is not known.
     * NOTE: $controllerClass has priority over this setting
     * NOTE 2: if $controllerClass is set, this will NOT return the extension key for that setting!
     *
     * @return string|null
     */
    public function getControllerExtKey(): ?string
    {
        return $this->controllerExtKey;
    }
    
    /**
     * Sets the extbase extension key for the controller used by this link.
     * Optional if the controller class name is not known.
     * NOTE: $controllerClass has priority over this setting
     *
     * @param string $controllerExtKey
     *
     * @return TypoLink
     */
    public function withControllerExtKey(string $controllerExtKey): TypoLink
    {
        $clone = clone $this;
        $clone->controllerExtKey = $controllerExtKey;
        return $clone;
    }
    
    /**
     * Returns the currently configured action name for the extbase controller used by this link.
     * @return string|null
     */
    public function getControllerAction(): ?string
    {
        return $this->controllerAction;
    }
    
    /**
     * Sets the extbase controller's action name this link should lead to
     *
     * @param string $controllerAction
     *
     * @return TypoLink
     */
    public function withControllerAction(string $controllerAction): TypoLink
    {
        $clone = clone $this;
        $clone->controllerAction = $controllerAction;
        return $clone;
    }
    
    /**
     * Returns the currently configured plugin name for this link.
     *
     * @return string
     */
    public function getPluginName(): ?string
    {
        return $this->pluginName;
    }
    
    /**
     * Optionally sets the name of the typo3 plugin name this link should lead to.
     *
     * @param string $pluginName
     *
     * @return TypoLink
     */
    public function withPluginName(string $pluginName): TypoLink
    {
        $clone = clone $this;
        $clone->pluginName = $pluginName;
        return $clone;
    }
    
    /**
     * Returns true if the link will contain a cHash, false if not
     * @return bool
     */
    public function useCHash(): bool
    {
        return $this->cHash;
    }
    
    /**
     * If set to FALSE the link will not contain a cHash
     *
     * @param bool $state
     *
     * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
     */
    public function withCHash(bool $state): TypoLink
    {
        $clone = clone $this;
        $clone->cHash = $state;
        return $clone;
    }
    
    /**
     * Returns the currently set arguments
     * @return array
     */
    public function getArgs(): iterable
    {
        return $this->args;
    }
    
    /**
     * Sets all currently configured arguments for the link
     *
     * @param array $args
     *
     * @return TypoLink
     */
    public function withArgs(iterable $args): TypoLink
    {
        $clone = clone $this;
        $clone->args = $args;
        return $clone;
    }
    
    /**
     * Adds a single argument and its value to the list of link arguments
     *
     * @param string $key   The key to set the value for
     * @param mixed  $value The value to set for the given key
     *
     * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
     */
    public function withAddedToArgs(string $key, $value): TypoLink
    {
        $clone = clone $this;
        $clone->args[$key] = $value;
        return $clone;
    }
    
    /**
     * Removes a single argument from the list of arguments
     *
     * @param string $key
     *
     * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
     */
    public function withRemovedFromArgs(string $key): TypoLink
    {
        $clone = clone $this;
        unset($clone->args[$key]);
        return $clone;
    }
    
    /**
     * Is used to set the language (L parameter) of the currently configured link.
     * Note: Using this will override the L parameter in your "args"
     *
     * @param \TYPO3\CMS\Core\Site\Entity\SiteLanguage|null|int|string $language
     *
     * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
     * @throws \LaborDigital\Typo3BetterApi\Link\LinkException
     */
    public function withLanguage($language): TypoLink
    {
        $clone = clone $this;
        if (!is_null($language)) {
            if (!is_object($language)) {
                $languages = $this->context->TypoContext->getSiteAspect()->getSite()->getLanguages();
                foreach ($languages as $lang) {
                    if (is_numeric($language) && $lang->getLanguageId() === (int)$language || strtolower($lang->getTwoLetterIsoCode()) == $language) {
                        $language = $lang;
                        break;
                    }
                }
            }
            if (!$language instanceof SiteLanguage) {
                throw new LinkException('The given language could not be found on site: ' . $this->context->TypoContext->getSiteAspect()->getSite()->getIdentifier());
            }
        }
        $clone->language = $language;
        return $clone;
    }
    
    /**
     * Returns the currently configured language or null
     *
     * @return \TYPO3\CMS\Core\Site\Entity\SiteLanguage|null
     */
    public function getLanguage(): ?SiteLanguage
    {
        return $this->language;
    }
    
    /**
     * Applies a link set which was previously defined in typoscript,
     * or using the LinkSetRepository in your php code.
     *
     * This will override existing data, so call it early in your link generation!
     *
     * @param string $setKey The name of the link set which should be applied
     *
     * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
     * @throws \LaborDigital\Typo3BetterApi\Link\LinkException
     */
    public function withSetApplied(string $setKey): TypoLink
    {
        return $this->context->LinkSetRepo->get($setKey)->__applyToLink($this);
    }
    
    /**
     * Uses the given configuration and builds a link as a simple string out of it
     *
     * @param array $options Additional configuration options
     *                       - relative (bool) FALSE: If set to true, the script will return the relative url, without
     *                       host or schema
     *                       - backend (bool) FALSE: If set to true, the script will forcefully build a backend url
     *                       - forMe (bool) FALSE: By default we create all links using a clean request, meaning
     *                       no namespacing of variables or extbase action/controller parameters. If you are using the
     *                       BetterActionController you have the option to use the controller's request which then
     *                       will automatically create a link which is only valid for the current controller / action.
     *                       The latter is the typo3 default way which I found really counter intuitive.
     *
     * @return string
     * @throws \LaborDigital\Typo3BetterApi\Link\LinkException
     */
    public function build(array $options = []): string
    {
        // Prepare options
        $options = Options::make($options, [
            'relative' => [
                'type'    => ['boolean'],
                'default' => false,
            ],
            'backend'  => [
                'type'    => ['boolean'],
                'default' => false,
            ],
            'forMe'    => [
                'type'    => ['boolean'],
                'default' => false,
            ],
        ]);
        
        // Prepare the uri builder
        if (!empty($this->uriBuilder)) {
            $ub = $this->uriBuilder;
        } else {
            $ub = $this->context->getUriBuilder();
            $ub->reset();
        }
        
        // Find the request to work with
        if ($options['forMe']) {
            $request = $this->controllerRequest;
        }
        if (empty($request)) {
            $request = $this->request;
        }
        
        // Check if for me can be used
        if ($options['forMe']) {
            if (empty($request)) {
                throw new LinkException('The "forMe" flag can only be used if you are inside a better api extbase controller, or if you manually supplied a Request object using setRequest()!');
            }
        }
        
        // Get our context's request object if nothing was supplied
        if (empty($request)) {
            $request = $this->context->getRequest();
        }
        
        // Inject our request into the uri builder
        $backupRequest = $ub->getRequest();
        $ub->setRequest($request);
        
        // Set config flags
        if (!$options['relative']) {
            $ub->setCreateAbsoluteUri(true);
        }
        $ub->setUseCacheHash($this->cHash);
        
        // Page id
        if (!empty($this->pid)) {
            $ub->setTargetPageUid($this->pid);
        } else {
            $ub->setTargetPageUid($this->context->TypoContext->getPidAspect()->getCurrentPid());
        }
        
        // Query string settings
        $ub->setAddQueryString($this->keepQuery);
        if ($this->keepQuery) {
            if (!empty($this->deniedQueryArgs)) {
                $excludedKeys = $this->deniedQueryArgs;
            } elseif (!empty($this->allowedQueryArgs)) {
                $excludedKeys = array_keys(parse_query(Path::makeUri(true)->getQuery()));
                $excludedKeys = array_diff($excludedKeys, $this->allowedQueryArgs);
            }
            if (isset($excludedKeys)) {
                $ub->setArgumentsToBeExcludedFromQueryString($excludedKeys);
            }
        }
        
        // Backup request data
        $backupController = $request->getControllerObjectName();
        $backupAction = $request->getControllerActionName();
        $backupPlugin = $request->getPluginName();
        
        // Determine if we have to use uriFor()
        $useUriFor = $options['forMe'];
        if (!empty($this->controllerClass)) {
            $request->setControllerObjectName($this->controllerClass);
            $useUriFor = true;
        } else {
            if (!empty($this->controllerName)) {
                $request->setControllerName($this->controllerName);
                $useUriFor = true;
            }
            if (!empty($this->controllerExtKey)) {
                $request->setControllerExtensionName($this->controllerExtKey);
                $useUriFor = true;
            }
        }
        if (!empty($this->controllerAction)) {
            $request->setControllerActionName($this->controllerAction);
            $useUriFor = true;
        }
        if (!empty($this->pluginName)) {
            $request->setPluginName($this->pluginName);
            $useUriFor = true;
        }
        
        // Resolve $pid. lookups in our args
        foreach ($this->args as $k => $v) {
            if (!is_string($v) || substr($v, 1, 4) !== 'pid.') {
                continue;
            }
            $this->args[$k] = $this->context->TypoContext->getPidAspect()->getPid($v);
        }
        
        // Inject the language into the args
        if (!empty($this->language)) {
            $this->args['L'] = $this->language->getLanguageId();
        }
        
        // Validate if we have all the required arguments
        if (!empty($this->requiredArgs)) {
            $missingArgs = [];
            foreach ($this->requiredArgs as $arg) {
                if (!array_key_exists($arg, $this->args)) {
                    $missingArgs[] = $arg;
                }
            }
            if (!empty($missingArgs)) {
                throw new LinkException('Could not build link, because it misses one or multiple arguments: ' . implode(', ', $missingArgs));
            }
        }
        
        // Validate if we have all the required fragment-arguments
        if (!empty($this->requiredFragmentArgs)) {
            if (!is_iterable($this->fragment)) {
                throw new LinkException('Could not build link, the applied link set requires (iterable) fragment arguments, but the fragment was set to: ' . gettype($this->fragment));
            }
            $missingArgs = [];
            foreach ($this->requiredFragmentArgs as $arg) {
                if (!array_key_exists($arg, (array)$this->fragment)) {
                    $missingArgs[] = $arg;
                }
            }
            if (!empty($missingArgs)) {
                throw new LinkException('Could not build link, because it misses one or multiple fragment arguments: ' . implode(', ', $missingArgs));
            }
        }
        
        // Execute uriFor if required
        if ($useUriFor) {
            // Do some adjustments if we are in cli mode, because typo3 checks if we are in frontend mode
            if (!$this->context->TypoContext->getEnvAspect()->isFrontend()) {
                
                // Automatically find the plugin name if there is none
                $plugin = $request->getPluginName();
                if (empty($plugin)) {
                    $plugin = $this->context->Extension->getPluginNameByAction(
                        $request->getControllerExtensionName(),
                        $request->getControllerName(),
                        $request->getControllerActionName()
                    );
                    $request->setPluginName($plugin);
                }
                
                // Automatically find pid if none is given
                if (empty($ub->getTargetPageUid())) {
                    $ub->setTargetPageUid(
                        $this->context->Extension->getTargetPidByPlugin(
                            $request->getControllerExtensionName(),
                            $request->getPluginName()
                        )
                    );
                }
            }
            
            // Note: Yes, we COULD use this output, but in cli and other edge cases the result will be wrong,
            // so we go the extra mile and let the build process run twice to be sure everything works smoothly
            $ub->uriFor(
                empty(($tmp = $request->getControllerActionName())) ? null : $tmp,
                $this->args,
                empty(($tmp = $request->getControllerName())) ? null : $tmp,
                empty(($tmp = $request->getControllerExtensionName())) ? null : $tmp,
                empty(($tmp = $request->getPluginName())) ? null : $tmp
            );
        } else {
            // Set arguments
            $ub->setArguments($this->args);
        }
        
        // Render the uri
        if ($options['backend'] && $this->context->TypoContext->getEnvAspect()->isBackend()) {
            $uri = $ub->buildBackendUri();
            if ($this->context->TypoContext->getEnvAspect()->isCli()) {
                $uri = preg_replace('~^(.*?)/index.php~', $this->context->TypoContext->getRequestAspect()->getHost() . '/' . TYPO3_mainDir . 'index.php', $uri);
            }
        } else {
            $uri = $ub->buildFrontendUri();
        }
        
        // Build the fragment / anchor
        if (!empty($this->fragment)) {
            $fragment = $this->fragment;
            if (is_iterable($this->fragment)) {
                $fPath = [];
                foreach ($this->fragment as $k => $v) {
                    $fPath[] = rawurlencode($k) . '/' . rawurlencode($v);
                }
                $fragment = '/' . implode('/', $fPath);
            }
            $uri .= '#' . $fragment;
        }
        
        // Clean up
        $request->setPluginName($backupPlugin);
        $request->setControllerActionName($backupAction);
        $request->setControllerObjectName($backupController);
        if (isset($backupRequest)) {
            $ub->setRequest($backupRequest);
        }
        if (empty($this->uriBuilder)) {
            $ub->reset();
        }
        
        // Done
        return $uri;
    }
    
    /**
     * Automatically call the build method if we are converted to a string
     *
     * @return string
     * @throws \LaborDigital\Typo3BetterApi\Link\LinkException
     */
    public function __toString()
    {
        return $this->build();
    }
    
    /**
     * Internal helper to inject the required elements of a link when applying a link set
     *
     * @param array $requiredArgs
     * @param array $requiredFragmentArgs
     *
     * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
     */
    public function __withRequiredElements(array $requiredArgs, array $requiredFragmentArgs)
    {
        $clone = clone $this;
        $clone->requiredArgs = $requiredArgs;
        $clone->requiredFragmentArgs = $requiredFragmentArgs;
        return $clone;
    }
}
