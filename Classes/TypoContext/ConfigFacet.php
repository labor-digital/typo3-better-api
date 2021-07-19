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
 * Last modified: 2020.05.12 at 12:51
 */


namespace LaborDigital\T3ba\TypoContext;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Tool\TypoContext\FacetInterface;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextException;
use LaborDigital\T3ba\Tool\TypoScript\TypoScriptService;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\State\ConfigState;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\Registry;

/**
 * Repository for the different, global configuration options in TYPO3
 */
class ConfigFacet implements FacetInterface
{
    use ContainerAwareTrait;
    
    /**
     * @var \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     */
    protected $context;
    
    /**
     * Holds the request attributes for all actions where we don't have a HTTP request
     *
     * @var array
     */
    protected $requestAttributeFallbackStorage = [];
    
    /**
     * ConfigFacet constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\TypoContext\TypoContext  $context
     */
    public function __construct(TypoContext $context)
    {
        $this->context = $context;
    }
    
    /**
     * @inheritDoc
     */
    public static function getIdentifier(): string
    {
        return 'config';
    }
    
    /**
     * Allows you to retrieve data from the ExtConfig config state object.
     *
     * @param   string      $key       Either a simple key or a colon separated path to find the value at
     * @param   null|mixed  $fallback  Returned if the $key was not found in the state
     *
     * @return mixed|null
     * @see ConfigState::get()
     */
    public function getConfigValue(string $key, $fallback = null)
    {
        return $this->getConfigState()->get($key, $fallback);
    }
    
    /**
     * Allows you to retrieve site based data from the config state object.
     * This is basically the same as getConfigValue() but automatically applies the typo.site.$SITE. prefix
     * to your key.
     *
     * @param   string      $key       Either a simple key or a colon separated path to find the value at
     * @param   null|mixed  $fallback  Returned if the $key was not found in the state
     *
     * @return mixed|null
     */
    public function getSiteBasedConfigValue(string $key, $fallback = null)
    {
        $site = $this->context->site()->getCurrent()->getIdentifier();
        
        return $this->getConfigValue('typo.site.' . $site . '.' . $key, $fallback);
    }
    
    /**
     * Returns the compiled ExtConfig state object.
     *
     * @return \Neunerlei\Configuration\State\ConfigState
     * @throws \LaborDigital\T3ba\Tool\TypoContext\TypoContextException
     */
    public function getConfigState(): ConfigState
    {
        if (! $this->hasService(ConfigState::class)) {
            throw new TypoContextException('The ConfigState object was not built and injected, yet! You are to early in the lifecycle!');
        }
        
        return $this->getService(ConfigState::class);
    }
    
    /**
     * Shortcut to TYPO3's system registry lookup method
     *
     * @param   string  $key           Key of the entry to return
     * @param   null    $defaultValue  Optional default value to use if this entry has never been set. Defaults to NULL.
     * @param   string  $namespace     optional extension key of extension otherwise "user_t3ba_config" is used
     *
     * @return mixed
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/SystemRegistry/Index.html
     * @see Registry::get()
     */
    public function getRegistryValue(string $key, $defaultValue = null, string $namespace = 'user_t3ba_config')
    {
        return $this->getService(Registry::class)->get($namespace, $key, $defaultValue);
    }
    
    /**
     * Shortcut to add or remove a value to/from TYPO3's system registry.
     * NOTE: Setting $value to NULL will remove the entry from the registry
     *
     * @param   string  $key        The key of the entry to set.
     * @param   mixed   $value      The value to set. This can be any PHP data type; The value has to be serializable!
     * @param   string  $namespace  optional extension key of extension otherwise "user_t3ba_config" is used
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/SystemRegistry/Index.html
     * @see Registry::get()
     */
    public function setRegistryValue(string $key, $value, string $namespace = 'user_t3ba_config'): self
    {
        if ($value === null) {
            $this->getService(Registry::class)->remove($namespace, $key);
        } else {
            $this->getService(Registry::class)->set($namespace, $key, $value);
        }
        
        return $this;
    }
    
    /**
     * Retrieve a single derived ServerRequest attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This works even if the script does not have a ServerRequest (for CLI or similar cases)
     * as we work with an internal fallback storage for the attributes
     *
     * @param   string      $attributeName  The name of the attribute to retrieve
     * @param   null|mixed  $fallback       The fallback value to return if the attribute is not registered
     *
     * @return mixed|null
     * @see \Psr\Http\Message\ServerRequestInterface
     * @see \LaborDigital\T3ba\TypoContext\RequestFacet
     */
    public function getRequestAttribute(string $attributeName, $fallback = null)
    {
        $request = $this->context->request()->getRootRequest();
        $localValue = $this->requestAttributeFallbackStorage[$attributeName] ?? $fallback;
        if ($request === null) {
            return $localValue;
        }
        
        return $request->getAttribute($attributeName, $localValue);
    }
    
    /**
     * Updates the global server request object with an additional attribute.
     * As the request is immutable we create a new copy of the request and reset the global
     * request instances in $GLOBALS.
     *
     * All attributes are stored ONLY for the current request, they are not persisted!
     *
     * @param   string  $attributeName  The name of the attribute to set
     * @param   mixed   $value          The value to set for the attribute
     *
     * @return ServerRequestInterface|null
     * @see \Psr\Http\Message\ServerRequestInterface
     * @see \LaborDigital\T3ba\TypoContext\RequestFacet
     */
    public function setRequestAttribute(string $attributeName, $value): ?ServerRequestInterface
    {
        $requestFacet = $this->context->request();
        $request = $requestFacet->getRootRequest();
        if (is_null($request)) {
            // Store the attribute locally
            $this->requestAttributeFallbackStorage[$attributeName] = $value;
        } else {
            // Store the value on the request
            $request = $request->withAttribute($attributeName, $value);
            $requestFacet->setRootRequest($request);
        }
        
        return $request;
    }
    
    /**
     * Returns the values of a certain environment variable or returns the $fallback if the
     * variable was not defined.
     *
     * @param   string       $varName   The name of the environment variable to look up
     * @param   string|null  $fallback  An optional fallback value to return if the environment variable is not set
     *
     * @return string|null
     */
    public function getEnvVar(string $varName, ?string $fallback = null): ?string
    {
        if (getenv($varName) === false) {
            return $fallback;
        }
        
        return getenv($varName);
    }
    
    /**
     * Returns information based on the Extension Configuration (defined in the ext_conf_template.txt)
     *
     * @param   string|null        $extensionName  The extension name / key to read the configuration for
     * @param   null|string|array  $key            The key / path to read from the configuration. This can either be
     *                                             a key or a path like "first.second.third" depending on the
     *                                             configuration in your ext_conf_template.txt file.
     * @param   null               $default        If either the $extensionName or the $key could not be found
     *                                             this value is returned instead. If this parameter is omitted NULL
     *                                             is returned in those cases.
     *
     * @return mixed|null
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ExtensionArchitecture/ConfigurationOptions/Index.html
     */
    public function getExtensionConfigValue(string $extensionName, $key = null, $default = null)
    {
        $path = Arrays::parsePath($key);
        try {
            return $this->getService(ExtensionConfiguration::class)->get($extensionName, implode('/', $path));
        } catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException $e) {
            return $default;
        }
    }
    
    /**
     * Returns the plugin / extension configuration for ext base extensions
     *
     * @param   string|null  $extensionName  The extension name / key to read the configuration for
     * @param   string|null  $pluginName     Optional plugin to look up.
     *
     * @return array
     */
    public function getExtBaseConfig(?string $extensionName = null, ?string $pluginName = null): array
    {
        return $this->getService(TypoScriptService::class)->getExtBaseSettings($extensionName, $pluginName);
    }
    
    /**
     * Shortcut to find a TypoScript configuration value using the TypoScriptService
     *
     * @param   null        $path     Either a key or a path like "config.lang" to query the hierarchy. If left
     *                                empty, the method will return the complete typoScript array.
     * @param   null|mixed  $default  By default the method returns null, if the queried value
     *                                was not found in the configuration. If this option is set, the given value
     *                                will be returned instead.
     * @param   array       $options  Additional options
     *                                - pid (integer): An optional pid to query the typoScript for.
     *                                - separator (string) ".": A separator trough which the path parts are
     *                                separated from each other
     *                                - getType (bool) FALSE: If set to TRUE the method will try return
     *                                the typoScript object's type instead of it's value.
     *                                The Type is normally stored as: key.key.type
     *                                while the value is stored as: key.key.type. <- Note the period
     *                                Not all elements have a type. If we don't fine one we will return the
     *                                "default" value Otherwise we will try to get the value, and if not set return
     *                                the type
     *
     * @return array|mixed|null
     */
    public function getTypoScriptValue($path = null, $default = null, array $options = [])
    {
        if (! is_null($default)) {
            $options['default'] = $default;
        }
        
        return $this->getService(TypoScriptService::class)->get($path, $options);
    }
    
    /**
     * This method can be used to retrieve ts config values from the configuration.
     *
     * @param   null        $path     Either a key or a path like "config.lang" to query the hierarchy. If left
     *                                empty, the method will return the complete typoScript array.
     * @param   null|mixed  $default  By default the method returns null, if the queried value
     *                                was not found in the configuration. If this option is set, the given value
     *                                will be returned instead.
     * @param   array       $options  Additional options
     *                                - pid (integer): An optional pid to query the typoScript for.
     *                                - separator (string) ".": A separator trough which the path parts are
     *                                separated from each other
     *                                - getType (bool) FALSE: If set to TRUE the method will try return
     *                                the typoScript object's type instead of it's value.
     *                                The Type is normally stored as: key.key.type
     *                                while the value is stored as: key.key.type. <- Note the period
     *                                Not all elements have a type. If we don't fine one we will return the
     *                                "default" value Otherwise we will try to get the value, and if not set return
     *                                the type
     *
     * @return array|mixed|null
     */
    public function getTsConfigValue($path = null, $default = null, array $options = [])
    {
        if (! is_null($default)) {
            $options['default'] = $default;
        }
        
        return $this->getService(TypoScriptService::class)->getTsConfig($path, $options);
    }
    
    /**
     * Checks if a TYPO3 feature toggle is enabled.
     *
     * @param   string  $featureName  The name of the feature toggle to check for
     *
     * @return bool
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/FeatureToggles/#using-the-api-as-extension-author
     */
    public function isFeatureEnabled(string $featureName): bool
    {
        return $this->makeInstance(Features::class)->isFeatureEnabled($featureName);
    }
}

