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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\Link;

use LaborDigital\Typo3BetterApi\Link\LinkBrowser\LinkSetRecordLinkBrowserHandler;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Extbase\Mvc\Request;

class LinkSetDefinition
{

    /**
     * The target page id
     *
     * @var int
     */
    protected $pid;

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
     * The arguments to build the link with
     *
     * @var array
     */
    protected $args = [];

    /**
     * True as long as the chash should be added to the generated link
     *
     * @var bool
     */
    protected $cHash = true;

    /**
     * A list of arguments that should be ignored when the chash is generated for this link
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
     * Holds the language this link should be generated for
     *
     * @var \TYPO3\CMS\Core\Site\Entity\SiteLanguage|null
     */
    protected $language;

    /**
     * Holds the setup for the link browser if it was given
     *
     * @var array|null
     */
    protected $linkBrowserConfig;

    /**
     * Returns the target page id or null
     *
     * @return int|string|null|array|callable
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Sets the target page id
     *
     * @param   int|string|array  $pid
     *
     * @return \LaborDigital\Typo3BetterApi\Link\LinkSetDefinition
     */
    public function setPid($pid): LinkSetDefinition
    {
        $this->pid = $pid;

        return $this;
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
     * @return LinkSetDefinition
     */
    public function setKeepQuery(bool $keepQuery): LinkSetDefinition
    {
        $this->keepQuery = $keepQuery;

        return $this;
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
     * @return \LaborDigital\Typo3BetterApi\Link\LinkSetDefinition
     */
    public function setAllowedQueryArgs(array $list): LinkSetDefinition
    {
        $this->allowedQueryArgs = array_values($list);

        return $this;
    }

    /**
     * If $keepQuery is set to TRUE. This list defines which query parameters should be removed.
     * All others will be kept.
     * NOTE: $queryBlacklist has priority over the whitelist. You can not use both together!
     *
     * @param   array  $list
     *
     * @return \LaborDigital\Typo3BetterApi\Link\LinkSetDefinition
     */
    public function setDeniedQueryArgs(array $list): LinkSetDefinition
    {
        $this->deniedQueryArgs = array_values($list);

        return $this;
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
    public function setFragmentGenerator(?string $generatorClass, string $method = 'generateFragment'): self
    {
        $this->fragmentGenerator = $generatorClass === null ? null : [$generatorClass, $method];

        return $this;
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
     * @return string|null|iterable
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
     * @return LinkSetDefinition
     * @throws \LaborDigital\Typo3BetterApi\Link\LinkException
     */
    public function setFragment($fragment): LinkSetDefinition
    {
        if (! is_array($fragment) && ! is_string($fragment) && $fragment !== null) {
            throw new LinkException('The given fragment is invalid!');
        }
        $this->fragment = is_string($fragment) ? trim(ltrim(trim($fragment), '#')) : $fragment;

        return $this;
    }

    /**
     * Adds a single fragment argument and its value to the link
     *
     * @param   string  $key    The key to set the value for
     * @param   mixed   $value  The value to set for the given key
     *
     * @return \LaborDigital\Typo3BetterApi\Link\LinkSetDefinition
     * @throws \LaborDigital\Typo3BetterApi\Link\LinkException
     */
    public function addToFragment(string $key, $value): LinkSetDefinition
    {
        if (! is_iterable($this->fragment)) {
            if (! empty($this->fragment)) {
                throw new LinkException('Can not add key: ' . $key
                                        . ' to the link\'s fragment, because the fragment is currently not iterable!');
            }
            $this->fragment = [];
        }
        $this->fragment[trim(ltrim(trim($key), '#'))] = $value;

        return $this;
    }

    /**
     * Removes a single argument from the list of fragment arguments
     *
     * @param   string  $key
     *
     * @return \LaborDigital\Typo3BetterApi\Link\LinkSetDefinition
     */
    public function removeFromFragment(string $key): LinkSetDefinition
    {
        if (! is_iterable($this->fragment)) {
            return $this;
        }
        unset($this->args[trim(ltrim(trim($key), '#'))]);

        return $this;
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
     * @return LinkSetDefinition
     */
    public function setControllerClass(string $controllerClass): LinkSetDefinition
    {
        $this->controllerClass = $controllerClass;

        return $this;
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
     * @return LinkSetDefinition
     */
    public function setControllerName(string $controllerName): LinkSetDefinition
    {
        $this->controllerName = $controllerName;

        return $this;
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
     * @return LinkSetDefinition
     */
    public function setControllerExtKey(string $controllerExtKey): LinkSetDefinition
    {
        $this->controllerExtKey = $controllerExtKey;

        return $this;
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
     * @return LinkSetDefinition
     */
    public function setControllerAction(string $controllerAction): LinkSetDefinition
    {
        $this->controllerAction = $controllerAction;

        return $this;
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
     * @return LinkSetDefinition
     */
    public function setPluginName(string $pluginName): LinkSetDefinition
    {
        $this->pluginName = $pluginName;

        return $this;
    }

    /**
     * Returns true if the link will contain a cHash, false if not
     *
     * @return bool
     */
    public function useCHash(): bool
    {
        return $this->cHash;
    }

    /**
     * If set to FALSE the link will not contain a cHash
     *
     * @param   bool  $state
     *
     * @return \LaborDigital\Typo3BetterApi\Link\LinkSetDefinition
     */
    public function setCHash(bool $state): LinkSetDefinition
    {
        $this->cHash = $state;

        return $this;
    }

    /**
     * Returns the list of arguments that should be excluded from cHash generation when the url is being build
     *
     * @return array
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
     * @return \LaborDigital\Typo3BetterApi\Link\LinkSetDefinition
     */
    public function setCHashExcludedArgs(array $argsToExclude): LinkSetDefinition
    {
        $this->cHashExcludedArgs = $argsToExclude;

        return $this;
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
     * Note: if you set the $value to "?" the argument will be registered as "required" placeholder
     * that has to be specified when the link is build.
     *
     * @param   array  $args
     *
     * @return LinkSetDefinition
     */
    public function setArgs(iterable $args): LinkSetDefinition
    {
        $this->args = $args;

        return $this;
    }

    /**
     * Returns the list of required argument and fragment elements that have to be set
     * in order to build the link successfully
     *
     * @return array
     */
    public function getRequiredElements(): array
    {
        $required = [];
        foreach ($this->args as $arg => $val) {
            if ($val === '?') {
                $required[] = $arg;
            }
        }
        if (is_iterable($this->fragment)) {
            foreach ($this->fragment as $arg => $val) {
                if ($val === '?') {
                    $required[] = 'fragment:' . $arg;
                }
            }
        }

        return $required;
    }

    /**
     * Adds a single argument and its value to the list of link arguments
     *
     * Note: if you set the $value to "?" the argument will be registered as "required" placeholder
     * that has to be specified when the link is build.
     *
     * @param   string  $key    The key to set the value for
     * @param   mixed   $value  The value to set for the given key
     *
     * @return \LaborDigital\Typo3BetterApi\Link\LinkSetDefinition
     */
    public function addToArgs(string $key, $value): LinkSetDefinition
    {
        $this->args[$key] = $value;

        return $this;
    }

    /**
     * Removes a single argument from the list of arguments
     *
     * @param   string  $key
     *
     * @return \LaborDigital\Typo3BetterApi\Link\LinkSetDefinition
     */
    public function removeFromArgs(string $key): LinkSetDefinition
    {
        unset($this->args[$key]);

        return $this;
    }

    /**
     * Is used to set the language (L parameter) of the currently configured link.
     * Note: Using this will override the L parameter in your "args"
     *
     * @param   \TYPO3\CMS\Core\Site\Entity\SiteLanguage|null  $language
     *
     * @return \LaborDigital\Typo3BetterApi\Link\LinkSetDefinition
     */
    public function setLanguage(?SiteLanguage $language): LinkSetDefinition
    {
        $this->language = $language;

        return $this;
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
     * This method allows you to register this link set as "selectable" in the TYPO3 link selector.
     * That allows you to directly link to records in your TCA or other input fields.
     *
     * IMPORTANT: You can only add link sets to the link browser if they have exactly ONE required
     * argument. This is, because we utilize the RecordLinkHandler under the hood to select a record
     * in the backend.
     *
     * @param   string  $label             A label for the generated tab in the link browser. Can be a translation
     *                                     label.
     * @param   string  $tableOrModelName  A database table name which can also be a short code like '...something"
     * @param   array   $options           Additional options for the setup
     *                                     - storagePid (string|int): an optional storage pid to force the link browser
     *                                     to. This can be either a numeric value or a pid identifier.
     *                                     - hidePageTree (bool) FALSE: If this is flag is set, the page tree will be
     *                                     hidden when the link browser is rendered
     *
     * @return $this
     * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.6/Feature-79626-IntegrateRecordLinkHandler.html
     */
    public function addToLinkBrowser(string $label, string $tableOrModelName, array $options = []): self
    {
        $this->linkBrowserConfig = [
            'handler' => LinkSetRecordLinkBrowserHandler::class,
            'label'   => $label,
            'table'   => $tableOrModelName,
            'options' => $options,
        ];

        return $this;
    }

    /**
     * Removes the previously defined link browser configuration for this set
     *
     * @return $this
     */
    public function clearLinkBrowserConfig(): self
    {
        $this->linkBrowserConfig = null;

        return $this;
    }

    /**
     * Returns either the currently set link browser configuration, or null if there is none
     *
     * @return array|null
     */
    public function getLinkBrowserConfig(): ?array
    {
        return $this->linkBrowserConfig;
    }

    /**
     * Internal helper which is called by the link to create a new link instance with this link set applied to it
     *
     * @param   \LaborDigital\Typo3BetterApi\Link\TypoLink  $link
     *
     * @return \LaborDigital\Typo3BetterApi\Link\TypoLink
     * @deprecated Will be renamed in v10
     */
    public function __applyToLink(TypoLink $link): TypoLink
    {
        // Inject all the properties
        if (! empty($this->pid)) {
            $link = $link->withPid($this->pid);
        }
        if (! empty($this->controllerName)) {
            $link = $link->withControllerName($this->controllerName);
        }
        if (! empty($this->controllerClass)) {
            $link = $link->withControllerClass($this->controllerClass);
        }
        if (! empty($this->controllerExtKey)) {
            $link = $link->withControllerExtKey($this->controllerExtKey);
        }
        if (! empty($this->controllerAction)) {
            $link = $link->withControllerAction($this->controllerAction);
        }
        if (! empty($this->pluginName)) {
            $link = $link->withPluginName($this->pluginName);
        }
        if (! empty($this->cHashExcludedArgs)) {
            $link = $link->withCHashExcludedArgs($this->cHashExcludedArgs);
        }
        if (is_bool($this->cHash)) {
            $link = $link->withCHash($this->cHash);
        }
        if (is_bool($this->keepQuery)) {
            $link = $link->withKeepQuery($this->keepQuery);
        }
        if (! empty($this->allowedQueryArgs)) {
            $link = $link->withAllowedQueryArgs($this->allowedQueryArgs);
        }
        if (! empty($this->deniedQueryArgs)) {
            $link = $link->withDeniedQueryArgs($this->deniedQueryArgs);
        }
        if (! empty($this->language)) {
            $link = $link->withLanguage($this->language);
        }
        if (! empty($this->fragmentGenerator)) {
            $link = $link->withFragmentGenerator(...$this->fragmentGenerator);
        }

        // Build args and required args
        if (! empty($this->args)) {
            $args = [];
            foreach ($this->args as $k => $v) {
                if (trim($v) !== '?') {
                    $args[$k] = $v;
                }
            }
            if (! empty($args)) {
                $link = $link->withArgs($args);
            }
        }
        if (! empty($this->fragment)) {
            if (is_string($this->fragment)) {
                $link = $link->withFragment($this->fragment);
            } else {
                $fragment = [];
                foreach ($this->fragment as $k => $v) {
                    if (trim($v) !== '?') {
                        $fragment[$k] = $v;
                    }
                }
                if (! empty($fragment)) {
                    $link = $link->withFragment($fragment);
                }
            }
        }

        $link = $link->withRequiredElements($this->getRequiredElements());

        // Done
        return $link;
    }
}
