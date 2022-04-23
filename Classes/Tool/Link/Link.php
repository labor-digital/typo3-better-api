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

namespace LaborDigital\T3ba\Tool\Link;

use GuzzleHttp\Psr7\Query;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\Link\Adapter\CacheHashCalculatorAdapter;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\Options\Options;
use Neunerlei\PathUtil\Path;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

/**
 * Class TypoLink
 *
 * Please note that this class is immutable!
 *
 * @package LaborDigital\T3ba\Tool\Link
 */
class Link implements NoDiInterface
{
    /**
     * @var \LaborDigital\T3ba\Tool\Link\LinkContext
     */
    protected $context;
    
    /**
     * @var Request|null
     */
    protected $controllerRequest;
    
    /**
     * The target page id
     *
     * @var int|string|null|array|callable
     */
    protected $pid;
    
    /**
     * The resolved, numeric target page id or null
     *
     * @var int|null
     */
    protected $resolvedPid;
    
    /**
     * The value of $pid that was used to resolve $resolvedPid with
     * This is an internal marker to check when we have to resolve the pid again.
     *
     * @var mixed
     */
    protected $resolvedPidGenerator;
    
    /**
     * True if the current query string should be appended to the new url
     *
     * @var bool
     */
    protected $keepQuery = false;
    
    /**
     * If $keepQuery is set to TRUE. This list defines which query parameters should be kept.
     * All others will be dropped.
     * NOTE: $queryBlacklist has priority over the whitelist. You can not use both together!
     *
     * @var array
     */
    protected $allowedQueryArgs = [];
    
    /**
     * If $keepQuery is set to TRUE. This list defines which query parameters should be removed.
     * All others will be kept.
     * NOTE: $queryBlacklist has priority over the whitelist. You can not use both together!
     *
     * @var array
     */
    protected $deniedQueryArgs = [];
    
    /**
     * The fragment / hash / anchor of the url
     *
     * @var string|iterable|null
     */
    protected $fragment;
    
    /**
     * The fragment generator to be used instead of the $fragment property.
     *
     * @var array|null
     */
    protected $fragmentGenerator;
    
    /**
     * Optional The controller class to create the request for
     *
     * @var string
     */
    protected $controllerClass;
    
    /**
     * Optional if the controller class name is not known.
     * NOTE: $controllerClass has priority over this setting
     *
     * @var string
     */
    protected $controllerName;
    
    /**
     * Optional if the controller class name is not known.
     * NOTE: $controllerClass has priority over this setting
     *
     * @var string
     */
    protected $controllerExtKey;
    
    /**
     * Optional The controller action to create the link for
     *
     * @var string
     */
    protected $controllerAction;
    
    /**
     * Optional The plugin name to create the link for
     *
     * @var string
     */
    protected $pluginName;
    
    /**
     * If set to true the link will always build using the "forMe" option in the build method.
     * This will try to add all ext base plugin arguments to the query.
     * This requires either the $request or the $controller... properties to be set
     *
     * @var bool
     */
    protected $isPluginTarget = false;
    
    /**
     * The arguments to build the link with
     *
     * @var array
     */
    protected $args = [];
    
    /**
     * A list of arguments that should be ignored when the chash is generated for this link
     *
     * @deprecated this will be removed in v11, as it does not have the desired effect anymore
     *
     * @var array
     */
    protected $cHashExcludedArgs = [];
    
    /**
     * Holds the user defined request object
     *
     * @var Request
     */
    protected $request;
    
    /**
     * Holds the user defined uri builder
     *
     * @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
     */
    protected $uriBuilder;
    
    /**
     * The list of fragment and argument elements that are "required" to build the link.
     * This is used when a linkSet was applied
     *
     * @var array
     */
    protected $requiredElements = [];
    
    /**
     * Holds the language this link should be generated for
     *
     * @var \TYPO3\CMS\Core\Site\Entity\SiteLanguage|null
     */
    protected $language;
    
    /**
     * Link constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\Link\LinkContext  $context
     */
    public function __construct(LinkContext $context)
    {
        $this->context = $context;
    }
    
    /**
     * Make sure the linked objects are cloned when we clone ourselves
     */
    public function __clone()
    {
        if (! empty($this->request)) {
            $this->request = clone $this->request;
        }
        if (! empty($this->uriBuilder)) {
            $this->uriBuilder = clone $this->uriBuilder;
        }
    }
    
    /**
     * Returns the currently registered pid.
     *
     * @param   bool  $resolved  By default the resolved, numeric pid will be returned.
     *                           If you set this to false, you retrieve the pid generator instead of the
     *                           value back.
     *
     * @return int|string|null|array|callable
     */
    public function getPid(bool $resolved = true)
    {
        if ($resolved) {
            if ($this->pid !== $this->resolvedPidGenerator) {
                $this->resolvedPid = $this->resolvePid();
                $this->resolvedPidGenerator = $this->pid;
            }
            
            return $this->resolvedPid;
        }
        
        return $this->pid;
    }
    
    /**
     * Sets the target page id
     *
     * You have multiple options to resolve the pid of a link:
     * - numeric value: When you pass a numeric value like an int 34 or "556" the script takes
     * that as the real id of the page you want to link to
     * - string value: You can pass a Pid identifier like "@pid.page.something" which will be resolved
     * based on your pid configuration.
     * - null: If no pid is given, the link is resolved on the current page id (DEFAULT)
     * - callable: Any kind of callable to resolve the pid dynamically based on current link object.
     * The callable will receive the link object as parameter and should return a numeric value or
     * pid identifier.
     * - array (Variant a): If you provide an array containing a class and method name, that are not
     * static (therefore are not callable): the script will instantiate the first item as an object
     * through the container and call the second item as method. The method also receives the current link
     * object and should behave in the same way a "callable" would.
     * - array (Variant b): If your link contains exactly ONE argument, you can pass an array containing
     * storage pids and their matching target pids as an array. For example you have records in a folder with pid 10
     * that map to a detail plugin on the page 20, as well as records in folder with pid 11 that map to a detail plugin
     * on the page 21 you can provide a map like: [10 => 20, 11 => 21].
     * NOTE: This works only if you provide extbase domain models or objects that have a public "getPid()" method.
     * If you are working with numeric values in your argument you have to provide a "table" key in the map,
     * that is used to resolve the storage pid of the record uid: ['table' => 'tx_my_table', 10 => 20, 11 => 21].
     * The 'table' can also be the class name of the extbase model we will map to a table name.
     * NOTE 2: If you work with multiple arguments the first, given argument in the list is used for pid resolution.
     * You can also specify the name of the argument by providing an "argument" key.
     *
     * As an example for arrays (Variant b):
     * // This will work, because $myExtbaseModel contains an extbase model
     * $link->withPid([10 => 20, 11 => 21])->withArgs(['model' => $myExtbaseModel])->build();
     *
     * // This will fail, because the link does not know how it should map the uid 2 to a storage pid
     * $link->withPid([10 => 20, 11 => 21])->withArgs(['model' => 2])->build();
     *
     * // To make this work you have to do this:
     * $link->withPid(['table' => 'tx_my_table', 10 => 20, 11 => 21])->withArgs(['model' => 2])->build();
     * // or:
     * $link->withPid(['table' => MyExtBaseModel::class, 10 => 20, 11 => 21])->withArgs(['model' => 2])->build();
     *
     * // This will fail, because there are multiple arguments present, and the first argument, "foo" is numeric
     * $link->withPid([10 => 20, 11 => 21])->withArgs(['foo' => 123, 'model' => $myExtbaseModel])->build();
     *
     * // To make this work, either use 'model' as the first argument:
     * $link->withPid([10 => 20, 11 => 21])
     *      ->withArgs(['model' => $myExtbaseModel, 'foo' => 123 ])->build();
     * // or define the argument which should be used for pid resolution:
     * $link->withPid(['argument' => 'model', 10 => 20, 11 => 21])
     *      ->withArgs(['foo' => 123, 'model' => $myExtbaseModel])->build();
     *
     * @param   int|string|null|array|callable  $pid
     *
     * @return $this
     */
    public function withPid($pid): self
    {
        $clone = clone $this;
        $clone->pid = $pid;
        
        return $clone;
    }
    
    /**
     * Returns the request which was set for this link.
     *
     * @param   bool  $alsoInternal  Be careful with this param.
     *                               If your set this to true the method will return the internal request
     *                               which is stored in the global context of the used "LinkService" instance.
     *                               If you change stuff in it you may break stuff.
     *
     * @return Request|null
     */
    public function getRequest(bool $alsoInternal): ?Request
    {
        if (! empty($this->request)) {
            return $this->request;
        }
        
        return $alsoInternal ? $this->context->getRequest() : null;
    }
    
    /**
     * Sets the request object of this link instance
     *
     * @param   Request  $request
     *
     * @return $this
     */
    public function withRequest(Request $request): self
    {
        $clone = clone $this;
        $clone->request = $request;
        
        return $clone;
    }
    
    /**
     * Returns the set uri builder for this link
     *
     * @param   bool  $alsoInternal  Be careful with this param.
     *                               If your set this to true the method will return the internal uri builder
     *                               which is stored in the global context of the used "LinkService" instance.
     *                               If you change stuff in it, you may break in other places.
     *
     * @return UriBuilder
     */
    public function getUriBuilder(bool $alsoInternal = false): ?UriBuilder
    {
        if (! empty($this->uriBuilder)) {
            return $this->uriBuilder;
        }
        
        return $alsoInternal ? $this->context->getUriBuilder() : null;
    }
    
    /**
     * Sets the uri builder of this link instance
     *
     * @param   \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder  $uriBuilder
     *
     * @return $this
     */
    public function withUriBuilder(UriBuilder $uriBuilder): self
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
     * @param   bool  $keepQuery
     *
     * @return Link
     */
    public function withKeepQuery(bool $keepQuery): self
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
        if (! empty($this->deniedQueryArgs)) {
            return [
                'type' => 'denied',
                'list' => $this->deniedQueryArgs,
            ];
        }
        
        if (! empty($this->allowedQueryArgs)) {
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
     * @param   array  $list
     *
     * @return $this
     */
    public function withAllowedQueryArgs(array $list): self
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
     * @param   array  $list
     *
     * @return $this
     */
    public function withDeniedQueryArgs(array $list): self
    {
        $clone = clone $this;
        $clone->deniedQueryArgs = array_values($list);
        
        return $clone;
    }
    
    /**
     * Sometimes you want to create the fragment of the link dynamically based on certain rules.
     * For this case you can provide a fragment generator. The generator will receive this link instance
     * to build the fragment with.
     *
     * NOTE: Defining a generator will replace all other defined fragments.
     * If you implement a generator it has to take care of the fragments itself.
     *
     * @param   string|null  $generatorClass  The name of a class that is used as generator
     * @param   string       $method          The method to call on the generator class.
     *                                        The method should return either an array or a string.
     *                                        Basically the same as if you would set withFragment()
     *
     * @return $this
     */
    public function withFragmentGenerator(?string $generatorClass, string $method = 'generateFragment'): self
    {
        $clone = clone $this;
        $clone->fragmentGenerator = $generatorClass === null ? null : [$generatorClass, $method];
        
        return $clone;
    }
    
    /**
     * Returns either the configured fragment generator callable, or null if there is none
     *
     * @return array|null
     */
    public function getFragmentGenerator(): ?array
    {
        return $this->fragmentGenerator;
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
     * @param   string|null|array  $fragment
     *
     * @return Link
     * @throws \LaborDigital\T3ba\Tool\Link\LinkException
     */
    public function withFragment($fragment): self
    {
        $clone = clone $this;
        if (! is_array($fragment) && ! is_string($fragment) && ! is_null($fragment)) {
            throw new LinkException('The given fragment is invalid!');
        }
        $clone->fragment = is_string($fragment) ? trim(ltrim(trim($fragment), '#')) : $fragment;
        
        return $clone;
    }
    
    /**
     * Adds a single fragment argument and its value to the link
     *
     * @param   string  $key    The key to set the value for
     * @param   mixed   $value  The value to set for the given key
     *
     * @return $this
     * @throws \LaborDigital\T3ba\Tool\Link\LinkException
     */
    public function withAddedToFragment(string $key, $value): self
    {
        $clone = clone $this;
        if (! is_iterable($clone->fragment)) {
            if (! empty($clone->fragment)) {
                throw new LinkException('Can not add key: ' . $key
                                        . ' to the link\'s fragment, because the fragment is currently not iterable!');
            }
            $clone->fragment = [];
        }
        $clone->fragment[trim(ltrim(trim($key), '#'))] = $value;
        
        return $clone;
    }
    
    /**
     * Removes a single argument from the list of fragment arguments
     *
     * @param   string  $key
     *
     * @return $this
     */
    public function withRemovedFromFragment(string $key): self
    {
        $clone = clone $this;
        if (! is_iterable($clone->fragment)) {
            return $clone;
        }
        unset($clone->args[trim(ltrim(trim($key), '#'))]);
        
        return $clone;
    }
    
    /**
     * Returns the currently configured extbase controller target-class for the link
     *
     * @return string
     */
    public function getControllerClass(): string
    {
        return $this->controllerClass;
    }
    
    /**
     * Can be used to set the target extbase controller, extension and vendor for this link.
     *
     * @param   string  $controllerClass
     *
     * @return Link
     */
    public function withControllerClass(string $controllerClass): self
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
     *
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
     * @param   string  $controllerName
     *
     * @return Link
     */
    public function withControllerName(string $controllerName): self
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
     * @param   string  $controllerExtKey
     *
     * @return Link
     */
    public function withControllerExtKey(string $controllerExtKey): self
    {
        $clone = clone $this;
        $clone->controllerExtKey = $controllerExtKey;
        
        return $clone;
    }
    
    /**
     * Returns the currently configured action name for the extbase controller used by this link.
     *
     * @return string|null
     */
    public function getControllerAction(): ?string
    {
        return $this->controllerAction;
    }
    
    /**
     * Sets the extbase controller's action name this link should lead to
     *
     * @param   string  $controllerAction
     *
     * @return Link
     */
    public function withControllerAction(string $controllerAction): self
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
     * @param   string  $pluginName
     *
     * @return Link
     */
    public function withPluginName(string $pluginName): self
    {
        $clone = clone $this;
        $clone->pluginName = $pluginName;
        
        return $clone;
    }
    
    /**
     * If set to true the link will always be build using the "forMe" option in the build method.
     * This will try to add all ext base plugin arguments to the query.
     * This requires either the $request or the $controller... properties to be set.
     *
     * Can be overwritten by setting the "forMe" option to false when the link is build
     *
     * @param   bool  $state
     *
     * @return $this
     */
    public function withPluginTarget(bool $state): self
    {
        $clone = clone $this;
        $clone->isPluginTarget = $state;
        
        return $clone;
    }
    
    /**
     * Returns true if the link should be build using the "forMe" option in the build method.
     *
     * @return bool
     */
    public function isPluginTarget(): bool
    {
        return $this->isPluginTarget;
    }
    
    /**
     * Returns the list of arguments that should be excluded from cHash generation when the url is being build
     *
     * @return array
     * @deprecated this will be removed in v11, as it does not have the desired effect anymore
     */
    public function getCHashExcludedArgs(): array
    {
        return $this->cHashExcludedArgs;
    }
    
    /**
     * Sets the list of arguments that should be excluded from cHash generation when the url is being build
     *
     * @param   array  $argsToExclude
     *
     * @return $this
     * @deprecated this will be removed in v11, as it does not have the desired effect anymore
     */
    public function withCHashExcludedArgs(array $argsToExclude): self
    {
        $clone = clone $this;
        $clone->cHashExcludedArgs = $argsToExclude;
        
        return $clone;
    }
    
    /**
     * Returns the currently set arguments
     *
     * @return array
     */
    public function getArgs(): iterable
    {
        return $this->args;
    }
    
    /**
     * Sets all currently configured arguments for the link
     *
     * @param   array  $args
     *
     * @return Link
     */
    public function withArgs(iterable $args): self
    {
        $clone = clone $this;
        $clone->args = $args;
        
        return $clone;
    }
    
    /**
     * Adds a single argument and its value to the list of link arguments
     *
     * @param   string  $key    The key to set the value for
     * @param   mixed   $value  The value to set for the given key
     *
     * @return $this
     */
    public function withAddedToArgs(string $key, $value): self
    {
        $clone = clone $this;
        $clone->args[$key] = $value;
        
        return $clone;
    }
    
    /**
     * Removes a single argument from the list of arguments
     *
     * @param   string  $key
     *
     * @return $this
     */
    public function withRemovedFromArgs(string $key): self
    {
        $clone = clone $this;
        unset($clone->args[$key]);
        
        return $clone;
    }
    
    /**
     * Is used to set the language (L parameter) of the currently configured link.
     * Note: Using this will override the L parameter in your "args"
     *
     * @param   \TYPO3\CMS\Core\Site\Entity\SiteLanguage|null|int|string  $language
     *
     * @return $this
     * @throws \LaborDigital\T3ba\Tool\Link\LinkException
     */
    public function withLanguage($language): self
    {
        $clone = clone $this;
        if (! is_null($language)) {
            if (! is_object($language)) {
                foreach ($this->context->getTypoContext()->site()->getCurrent()->getLanguages() as $lang) {
                    if (
                        (is_numeric($language) && $lang->getLanguageId() === (int)$language)
                        || strtolower($lang->getTwoLetterIsoCode()) === $language
                    ) {
                        $language = $lang;
                        break;
                    }
                }
            }
            if (! $language instanceof SiteLanguage) {
                throw new LinkException(
                    'The given language could not be found on site: '
                    . $this->context->getTypoContext()->site()->getCurrent()->getIdentifier()
                );
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
     * Applies a link set which was previously defined in typoScript,
     * or using the LinkSetRepository in your php code.
     *
     * This will override existing data, so call it early in your link generation!
     *
     * @param   string  $setKey  The name of the link set which should be applied
     *
     * @return $this
     */
    public function withSetApplied(string $setKey): self
    {
        return $this->context->getDefinitions($setKey)->applyToLink($this);
    }
    
    /**
     * Internal method to set the list of argument and fragment keys that are required to build the link
     * successfully. This is used internally to inject the elements from a link set.
     *
     * @param   array  $requiredElements
     *
     * @return $this
     * @internal
     */
    public function withRequiredElements(array $requiredElements): self
    {
        $clone = clone $this;
        $clone->requiredElements = $requiredElements;
        
        return $clone;
    }
    
    /**
     * Returns the list of element keys that have to be present to build the link
     *
     * @return array
     */
    public function getRequiredElements(): array
    {
        return $this->requiredElements;
    }
    
    /**
     * Uses the given configuration and builds a link as a simple string out of it
     *
     * @param   array  $options  Additional configuration options
     *                           - relative (bool) FALSE: If set to true, the script will return the relative url,
     *                           without host or schema
     *                           - backend (bool) FALSE: If set to true, the script will forcefully build a backend url
     *                           - forMe (bool) FALSE: By default we create all links using a clean request, meaning
     *                           no namespacing of variables or extbase action/controller parameters. If you are using
     *                           the BetterActionController you have the option to use the controller's request which
     *                           then will automatically create a link which is only valid for the current controller /
     *                           action. The latter is the typo3 default way which I found really counter intuitive.
     *
     * @return string
     * @throws \LaborDigital\T3ba\Tool\Link\LinkException
     * @todo this should be extracted into it's own builder class
     */
    public function build(array $options = []): string
    {
        // Prepare options
        $options = Options::make($options, [
            'relative' => [
                'type' => ['boolean'],
                'default' => false,
            ],
            'backend' => [
                'type' => ['boolean'],
                'default' => false,
            ],
            'forMe' => [
                'type' => ['boolean'],
                'default' => $this->isPluginTarget(),
            ],
        ]);
        $typoContext = $this->context->getTypoContext();
        
        // Prepare the uri builder
        if (! empty($this->uriBuilder)) {
            $ub = $this->uriBuilder;
        } else {
            $ub = $this->context->getUriBuilder();
            $ub->reset();
        }
        
        // Find the request to work with
        if ($options['forMe']) {
            $request = $this->controllerRequest;
        }
        if (! isset($request)) {
            $request = $this->request;
        }
        
        // Check if for me can be used
        if ($options['forMe'] && $request === null) {
            throw new LinkException('The "forMe" flag can only be used if you are inside a T3BA extbase controller, or if you manually supplied a Request object using setRequest()!');
        }
        
        // Get our context's request object if nothing was supplied
        if ($request === null) {
            $request = $this->context->getRequest();
        }
        
        // Inject our request into the uri builder
        $backupRequest = $ub->getRequest();
        $ub->setRequest($request);
        
        $ub->setCreateAbsoluteUri(! $options['relative']);
        $ub->setTargetPageUid($this->getPid() ?? $typoContext->pid()->getCurrent());
        
        // Query string settings
        $ub->setAddQueryString($this->keepQuery);
        if ($this->keepQuery) {
            if (! empty($this->deniedQueryArgs)) {
                $excludedKeys = $this->deniedQueryArgs;
            } elseif (! empty($this->allowedQueryArgs)) {
                $excludedKeys = array_keys(Query::parse(Path::makeUri(true)->getQuery()));
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
        if (! empty($this->controllerClass)) {
            $request->setControllerObjectName($this->controllerClass);
            $useUriFor = true;
        } else {
            if (! empty($this->controllerName)) {
                $request->setControllerName($this->controllerName);
                $useUriFor = true;
            }
            
            if (! empty($this->controllerExtKey)) {
                $request->setControllerExtensionName($this->controllerExtKey);
                $useUriFor = true;
            }
        }
        
        if (! empty($this->controllerAction)) {
            $request->setControllerActionName($this->controllerAction);
            $useUriFor = true;
        }
        
        if (! empty($this->pluginName)) {
            $request->setPluginName($this->pluginName);
            $useUriFor = true;
        }
        
        // Resolve $pid. lookups in our args
        $pidFacet = $typoContext->pid();
        foreach ($this->args as $k => $v) {
            if (empty($v) || ! is_string($v) || ! $pidFacet->has($v)) {
                continue;
            }
            $this->args[$k] = $pidFacet->get($v);
        }
        
        // Inject the language into the args
        if (! empty($this->language)) {
            $this->args['L'] = $this->language->getLanguageId();
        }
        
        // Validate required elements
        $requiredFragments = array_filter($this->getRequiredElements(), static function ($v) {
            return strpos($v, 'fragment:') === 0;
        });
        $requiredArgs = array_diff($this->getRequiredElements(), $requiredFragments);
        
        // Validate if we have all the required arguments
        $missingArgs = array_diff_key(array_fill_keys($requiredArgs, true), $this->args);
        if (! empty($missingArgs)) {
            throw new LinkException('Could not build link, because it misses one or multiple arguments: '
                                    . implode(', ', array_keys($missingArgs)));
        }
        
        // While generating links for the frontend, T3 tends to taint the page renderer instance
        // while the user is in the backend (for example setting the language to "default").
        // Therefore, we will save the page renderer and temporarily provide a clone for the link generation.
        try {
            if ($typoContext->env()->isBackend()) {
                $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
                GeneralUtility::setSingletonInstance(PageRenderer::class, clone $pageRenderer);
            }
            
            // Execute uriFor if required
            if ($useUriFor) {
                // Do some adjustments if we are in cli mode, because typo3 checks if we are in frontend mode
                if (! $typoContext->env()->isFrontend()) {
                    // Automatically find the plugin name if there is none
                    $plugin = $request->getPluginName();
                    if (empty($plugin)) {
                        $plugin = $this->context->getExtensionService()->getPluginNameByAction(
                            $request->getControllerExtensionName(),
                            $request->getControllerName(),
                            $request->getControllerActionName()
                        );
                        $request->setPluginName($plugin);
                    }
                    
                    // Automatically find pid if none is given
                    if ($ub->getTargetPageUid() === null) {
                        $ub->setTargetPageUid(
                            $this->context->getExtensionService()->getTargetPidByPlugin(
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
            if ($options['backend'] && $typoContext->env()->isBackend()) {
                $uri = $ub->buildBackendUri();
                if ($typoContext->env()->isCli()) {
                    $uri = preg_replace('~^(.*?)/index.php~',
                        $typoContext->request()->getHost() . '/' . TYPO3_mainDir . 'index.php',
                        $uri
                    );
                }
            } elseif (! empty($this->cHashExcludedArgs)) {
                // Simulate the ignored cHash fields
                $uri = CacheHashCalculatorAdapter::runWithConfiguration([
                    'excludedParameters' => $this->cHashExcludedArgs,
                ], function () use ($ub) {
                    return $ub->buildFrontendUri();
                });
            } else {
                $uri = $ub->buildFrontendUri();
            }
            
        } finally {
            if (isset($pageRenderer)) {
                GeneralUtility::setSingletonInstance(PageRenderer::class, $pageRenderer);
            }
        }
        
        // Build the fragment / anchor
        $fragment = $this->fragment;
        if ($this->fragmentGenerator !== null) {
            $fragment = call_user_func(NamingUtil::resolveCallable($this->fragmentGenerator), $this);
        }
        if (! empty($fragment)) {
            if (is_iterable($fragment)) {
                $fPath = [];
                foreach ($fragment as $k => $v) {
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
        if (! isset($this->uriBuilder)) {
            $ub->reset();
        }
        
        // Done
        return $uri;
    }
    
    /**
     * Automatically call the build method if we are converted to a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->build();
    }
    
    /**
     * Internal helper to resolve the pid of the link into an actual, numeric value
     *
     * @return int|null
     * @throws \LaborDigital\T3ba\Tool\Link\LinkException
     */
    protected function resolvePid(): ?int
    {
        if (empty($this->pid) && $this->pid !== 0) {
            return null;
        }
        
        $typoContext = $this->context->getTypoContext();
        
        // Resolve callable
        if (is_callable($this->pid)
            || ((is_array($this->pid) && class_exists((string)$this->pid[0]))
                || (is_string($this->pid) && strpos($this->pid, '->') !== false))) {
            $pid = call_user_func(NamingUtil::resolveCallable($this->pid), $this);
        } elseif (is_array($this->pid)) {
            // Translate the map keys into real pids so we can do a lookup for the correct value
            $keys = array_map(static function ($k) use ($typoContext) {
                if ($k === 'table' || $k === 'argument') {
                    return $k;
                }
                
                return $typoContext->Pid()->get($k);
            }, array_keys($this->pid));
            
            $pids = array_combine($keys, (array)$this->pid);
            
            // Try to fetch the storage pid based on the given argument
            $arg = isset($pids['argument']) ? $this->args[$pids['argument']] : reset($this->args);
            $storagePid = 0;
            
            if (is_array($arg) && isset($arg['pid'])) {
                $storagePid = $arg['pid'];
            } elseif (is_object($arg) && method_exists($arg, 'getPid')) {
                $storagePid = $arg->getPid();
            } elseif (is_numeric($arg)) {
                if (! isset($pids['table'])) {
                    throw new LinkException(
                        'Failed to map argument: ' . $arg
                        . ' to a storage pid, because the "table" key is missing in the setPid() array of the link!');
                }
                
                // Resolve a model name to a table name
                $table = $pids['table'];
                if (class_exists($table)) {
                    $table = NamingUtil::resolveTableName($table);
                }
                
                $storagePid = $typoContext->di()->cs()->db
                                  ->getQuery($table)
                                  ->withWhere(['uid' => is_array($arg) && isset($arg['uid']) ? $arg['uid'] : $arg])
                                  ->getFirst(['pid'])['pid'] ?? 0;
            }
            
            if (isset($pids[$storagePid])) {
                $pid = $pids[$storagePid];
            } else {
                $argString = is_object($arg) ? get_class($arg) : (string)$arg;
                throw new LinkException('Failed to map argument value: "' . $argString .
                                        '" (key: ' . array_search($arg, $this->args, true) . ')' .
                                        ' with storage pid: ' . $storagePid . ' to a link pid!');
            }
            
        } else {
            $pid = $this->pid;
        }
        
        if (! empty($pid) && ! is_numeric($pid)) {
            $pid = $typoContext->pid()->get($pid);
        }
        
        return $pid;
    }
}
