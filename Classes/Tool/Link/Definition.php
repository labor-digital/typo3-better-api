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
 * Last modified: 2021.05.10 at 18:57
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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\T3ba\Tool\Link;

use Closure;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\Link\LinkBrowser\LinkBrowserHandler;
use TypeError;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class Definition implements NoDiInterface
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
     * A list of arguments that should be ignored when the chash is generated for this link
     *
     * @var array
     */
    protected $cHashExcludedArgs = [];
    
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
     * @return int|string|null
     */
    public function getPid()
    {
        return $this->pid;
    }
    
    /**
     * Sets the target page id
     *
     * @param   int|string|null|array|callable  $pid  A multitude of options. IMPORTANT: Closures or callbacks
     *                                                with objects as target don't work in a link-set. Please
     *                                                refer to the TYPO3 callback syntax, or provide a static callback
     *
     * @return $this
     * @see \LaborDigital\T3ba\Tool\Link\Link::withPid() for the list of possible options that can
     *                                                       be applied for $pid.
     */
    public function setPid($pid): self
    {
        if ($pid instanceof Closure || (is_array($pid) && is_object($pid[0]))) {
            throw new TypeError('$pid can not be a Closure or a callback using a object as target. Please refer to the TYPO3 callback syntax, or provide a static callback.');
        }
        
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
     * @return Definition
     */
    public function setKeepQuery(bool $keepQuery): self
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
     * @return $this
     */
    public function setAllowedQueryArgs(array $list): self
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
     * @return $this
     */
    public function setDeniedQueryArgs(array $list): self
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
     * @return Definition
     * @throws \LaborDigital\T3ba\Tool\Link\LinkException
     */
    public function setFragment($fragment): self
    {
        if (! is_array($fragment) && ! is_string($fragment) && ! is_null($fragment)) {
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
     * @return $this
     * @throws \LaborDigital\T3ba\Tool\Link\LinkException
     */
    public function addToFragment(string $key, $value): self
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
     * @return $this
     */
    public function removeFromFragment(string $key): self
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
     * @return Definition
     */
    public function setControllerClass(string $controllerClass): self
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
     * @return Definition
     */
    public function setControllerName(string $controllerName): self
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
     * @return Definition
     */
    public function setControllerExtKey(string $controllerExtKey): self
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
     * @return Definition
     */
    public function setControllerAction(string $controllerAction): self
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
     * @return Definition
     */
    public function setPluginName(string $pluginName): self
    {
        $this->pluginName = $pluginName;
        
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
     * @return $this
     */
    public function setCHashExcludedArgs(array $argsToExclude): self
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
     * @return Definition
     */
    public function setArgs(iterable $args): self
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
     * @return $this
     */
    public function addToArgs(string $key, $value): self
    {
        $this->args[$key] = $value;
        
        return $this;
    }
    
    /**
     * Removes a single argument from the list of arguments
     *
     * @param   string  $key
     *
     * @return $this
     */
    public function removeFromArgs(string $key): self
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
     * @return $this
     */
    public function setLanguage(?SiteLanguage $language): self
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
     *                                     - basePid (string|int): an optional storage pid to force the link browser
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
            'handler' => LinkBrowserHandler::class,
            'label' => $label,
            'table' => $tableOrModelName,
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
     * @param   \LaborDigital\T3ba\Tool\Link\Link  $link
     *
     * @return \LaborDigital\T3ba\Tool\Link\Link
     */
    public function applyToLink(Link $link): Link
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
        
        // Build args and fragment
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
        
        return $link->withRequiredElements($this->getRequiredElements());
    }
}
