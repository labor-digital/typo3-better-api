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


namespace LaborDigital\T3ba\ExtConfigHandler\Core;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigConfigurator;
use LaborDigital\T3ba\Tool\Log\BeLogWriter;
use LaborDigital\T3ba\Tool\Log\BetterFileWriter;
use LaborDigital\T3ba\Tool\Log\StreamWriter;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\Options\Options;

class TypoCoreConfigurator extends AbstractExtConfigConfigurator implements NoDiInterface
{
    use TypoContextAwareTrait;
    use LogConfigTrait;
    
    /**
     * The list of registered x classes
     *
     * @var array
     */
    protected $xClasses = [];
    
    /**
     * The list of registered cache configurations
     *
     * @var array
     */
    protected $cacheConfigurations = [];
    
    /**
     * Stores the list of feature toggles that should be enabled by default, if not explicilty disabled.
     *
     * @var array
     */
    protected $featureToggleDefaults = [];
    
    /**
     * The list of enabled, and disabled feature toggles
     *
     * @var array
     */
    protected $featureToggles = [];
    
    /**
     * Registers a xClass override for a given class
     *
     * @param   string  $classToOverride      The class to override with the xClass
     * @param   string  $classToOverrideWith  The class to use as a xClass
     *
     * @return \LaborDigital\T3ba\ExtConfigHandler\Core\TypoCoreConfigurator
     * @see https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/Xclasses/Index.html
     */
    public function registerXClass(string $classToOverride, string $classToOverrideWith): self
    {
        $this->xClasses[$classToOverride] = [
            'className' => $classToOverrideWith,
        ];
        
        return $this;
    }
    
    /**
     * Returns the list of registered x classes, where the key is the class to be overwritten, and the value the class to overwrite with.
     *
     * @return array
     */
    public function getXClasses(): array
    {
        return array_column($this->xClasses, 'className');
    }
    
    /**
     * Removes the override for a previously registered x class
     *
     * @param   string  $classToOverride
     *
     * @return $this
     */
    public function removeXClass(string $classToOverride): self
    {
        unset($this->xClasses[$classToOverride]);
        
        return $this;
    }
    
    /**
     * Registers a new cache configuration to TYPO3's caching framework.
     *
     * Pro Tip: If you want to override an existing cache implementation do it like this:
     * $config = $configuratior->getCacheconfig('foo');
     * $config['options']['groups'] = ['pages'];
     * $configurator->registerCache(...$config);
     *
     * @param   string  $identifier  The cache identifier which is used to retrieve the cache instance later
     * @param   string  $frontend    The classname of the frontend to use
     * @param   string  $backend     The classname of the backend to use
     * @param   array   $options     Additional options for this cache
     *                               - options: (array) default: [] | Additional configuration for your backend.
     *                               Take a look a the typo3 documentation to see which options are supported.
     *                               - groups: (array|string) default: [] | One or multiple cache groups that should
     *                               be able to flush this cache. Allowed values are "all", "system" and "pages"
     *
     * @return \LaborDigital\T3ba\ExtConfigHandler\Core\TypoCoreConfigurator
     *
     * @see https://stackoverflow.com/a/39446841
     * @see https://docs.typo3.org/typo3cms/CoreApiReference/latest/ApiOverview/CachingFramework/Developer/Index.html
     */
    public function registerCache(
        string $identifier,
        string $frontend,
        string $backend,
        array $options = []
    ): self
    {
        $options = Options::make($options,
            [
                'options' => [[]],
                'groups' => [
                    'type' => ['string', 'array'],
                    'filter' => static function ($v) {
                        return is_array($v) ? $v : [$v];
                    },
                    'default' => [],
                    'validator' => static function ($v) {
                        if (! empty(array_filter($v, static function ($v) {
                            return ! in_array($v, ['all', 'system', 'pages'], true);
                        }))) {
                            return 'Your cache groups are invalid! Only the values all, system and pages are allowed!';
                        }
                        
                        return true;
                    },
                ],
            ]);
        
        $this->cacheConfigurations[$this->context->replaceMarkers($identifier)] = [
            'frontend' => $frontend,
            'backend' => $backend,
            'options' => $this->context->replaceMarkers($options['options']),
            'groups' => $this->context->replaceMarkers($options['groups']),
        ];
        
        return $this;
    }
    
    /**
     * Retrieves a previously configured cache configuration.
     *
     * @param   string  $identifier
     *
     * @return array|null
     */
    public function getCacheConfig(string $identifier): ?array
    {
        $identifier = $this->context->replaceMarkers($identifier);
        
        if (! isset($this->cacheConfigurations[$identifier])) {
            return null;
        }
        
        $config = $this->cacheConfigurations[$identifier];
        
        return [
            'identifier' => $identifier,
            'frontend' => $config['frontend'],
            'backend' => $config['backend'],
            'options' => [
                'options' => $config['options'],
                'groups' => $config['groups'],
            ],
        ];
    }
    
    /**
     * Registers a new logfile writer in the system. It utilizes our internal
     * better file writer that has built-in log rotation capabilities.
     *
     * @param   array|null  $options  Additional log configuration options
     *                                - key string: Allows you to provide a unique key for this configuration
     *                                so other configuration classes may override your config. The key will also be used
     *                                for the file name generation
     *                                - logLevel int: This is equivalent with one of the LogLevel constants.
     *                                Default:
     *                                -- LogLevel::INFO: if the TYPO3 context is set to "development",
     *                                a frontend request is executed and TYPO3_CONF_VARS.FE.debug is truthy,
     *                                or a backend/install/CLI request is executed and TYPO3_CONF_VARS.BE.debug is truthy,
     *                                -- LogLevel::ERROR in all other cases
     *                                - namespace string (Vendor/ExtKey): The PHP namespace for the logger to be active in.
     *                                This can be either a class name or a part of a php namespace. If an empty
     *                                string is given the configuration is applied globally
     *                                - writer array: the writer configuration array for the configured loglevel
     *                                - processor array: the processor configuration array for the configured loglevel
     *                                - logRotation bool (TRUE): By default the log files will be rotated once a day.
     *                                If you want to disable the log rotation set this option to false.
     *                                - global bool: If this flag is set the writer is set as a global "default",
     *                                this will disable the "namespace" and "processor" options, tho!
     *                                - filesToKeep int (5): If logRotation is enabled, this defines how many
     *                                files will be kept before they are deleted
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Logging/Configuration/Index.html
     * @see \TYPO3\CMS\Core\Log\LogLevel
     */
    public function registerFileLog(?array $options = null): self
    {
        $additionalDefinition = [
            'logRotation' => [
                'type' => 'bool',
                'default' => true,
            ],
            'filesToKeep' => [
                'type' => 'int',
                'default' => 5,
            ],
        ];
        
        $options = $this->prepareLogConfig($options, $additionalDefinition);
        
        $options['writer'] = [
            BetterFileWriter::class => [
                'logRotation' => $options['logRotation'],
                'filesToKeep' => $options['filesToKeep'],
                'name' => is_numeric($options['key']) ? md5($options['key']) : $options['key'],
            ],
        ];
        
        return $this->pushLogConfig($options);
    }
    
    /**
     * Registers a new stream logger the system. It works quite similar to the syslog writer that is available in the
     * TYPO3 core, but allows you to define the stream to write to. By default it will write to php://stdout which
     * means it is perfect for logging inside of docker containers.
     *
     * @param   array|null  $options  Additional log configuration options
     *                                - logLevel int: This is equivalent with one of the LogLevel constants.
     *                                Default:
     *                                -- LogLevel::INFO: if the TYPO3 context is set to "development",
     *                                a frontend request is executed and TYPO3_CONF_VARS.FE.debug is truthy,
     *                                or a backend/install/CLI request is executed and TYPO3_CONF_VARS.BE.debug is truthy,
     *                                -- LogLevel::ERROR in all other cases
     *                                - namespace string (Vendor/ExtKey): The PHP namespace for the logger to be active in.
     *                                This can be either a class name or a part of a php namespace. If an empty
     *                                string is given the configuration is applied globally
     *                                - global bool: If this flag is set the writer is set as a global "default",
     *                                this will disable the "namespace" and "processor" options, tho!
     *                                - processor array: the processor configuration array for the configured loglevel
     *                                - stream string (php://stdout): Allows you to configure the stream to write to.
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Logging/Configuration/Index.html
     * @see \TYPO3\CMS\Core\Log\LogLevel
     */
    public function registerStreamLogger(?array $options = null): self
    {
        $additionalDefinition = [
            'stream' => [
                'type' => ['null', 'string'],
                'default' => null,
            ],
        ];
        
        $options = $this->prepareLogConfig($options, $additionalDefinition);
        
        $options['writer'] = [
            StreamWriter::class => [
                'stream' => $options['stream'],
            ],
        ];
        
        return $this->pushLogConfig($options);
    }
    
    /**
     * Registers a new backend log logger in the system. This type of logger is basically a hybrid of the
     * DatabaseWriter in the PSR-3 logging implementation, and the old-school $GLOBALS['BE_USER']->writelog() logger.
     * It writes the log entries always in the sys_log table, but fills the field sets of both implementations while
     * doing so.
     *
     * @param   array|null  $options  Additional log configuration options
     *                                - logLevel int: This is equivalent with one of the LogLevel constants.
     *                                Default:
     *                                -- LogLevel::INFO: if the TYPO3 context is set to "development",
     *                                a frontend request is executed and TYPO3_CONF_VARS.FE.debug is truthy,
     *                                or a backend/install/CLI request is executed and TYPO3_CONF_VARS.BE.debug is truthy,
     *                                -- LogLevel::ERROR in all other cases
     *                                - namespace string (Vendor/ExtKey): The PHP namespace for the logger to be active in.
     *                                This can be either a class name or a part of a php namespace. If an empty
     *                                string is given the configuration is applied globally
     *                                - global bool: If this flag is set the writer is set as a global "default",
     *                                this will disable the "namespace" and "processor" options, tho!
     *                                - processor array: the processor configuration array for the configured loglevel
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Logging/Configuration/Index.html
     * @see \TYPO3\CMS\Core\Log\LogLevel
     */
    public function registerBeLogLogger(?array $options = null): self
    {
        $options = $this->prepareLogConfig($options);
        
        $options['writer'] = [
            BeLogWriter::class => [],
        ];
        
        return $this->pushLogConfig($options);
    }
    
    /**
     * Registers any kind of log configuration based on your input.
     *
     * @param   array  $options  The options for your log configuration
     *                           - key string: Allows you to provide a unique key for this configuration
     *                           so other configuration classes may override your config
     *                           - logLevel int (7|3): This is equivalent with one of the LogLevel constants.
     *                           It defines the minimal viable severity that should be logged, all levels with a higher
     *                           number that the given level will be be ignored
     *                           - namespace string (Vendor/ExtKey): The PHP namespace for the logger to be active in.
     *                           This can be either a class name or a part of a php namespace. If an empty
     *                           string is given the configuration is applied globally
     *                           - writer array: the writer configuration array for the configured loglevel
     *                           - processor array: the processor configuration array for the configured loglevel
     *
     * @return \LaborDigital\T3ba\ExtConfigHandler\Core\TypoCoreConfigurator
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Logging/Configuration/Index.html
     * @see \TYPO3\CMS\Core\Log\LogLevel
     */
    public function registerLogWriter(array $options): self
    {
        return $this->pushLogConfig($this->prepareLogConfig($options));
    }
    
    /**
     * Used to enable a feature by default in your extension. Features enabled by default,
     * must be disabled explicitly by using disableFeature() again. This method is used by extension authors.
     *
     * @param   string  $featureName  The name of the feature you want to enable
     *
     * @return $this
     * @see disableFeature()
     */
    public function enableFeatureByDefault(string $featureName): self
    {
        $this->featureToggleDefaults[$featureName] = true;
        
        return $this;
    }
    
    /**
     * Enables a TYPO3 feature toggle.
     *
     * @param   string  $featureName  The name of the feature you want to enable
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/FeatureToggles/#using-the-api-as-extension-author
     * @see \LaborDigital\T3ba\TypoContext\ConfigFacet::isFeatureEnabled() to check if a feature was enabled
     */
    public function enableFeature(string $featureName): self
    {
        $this->featureToggles[$featureName] = true;
        
        return $this;
    }
    
    /**
     * Disables a TYPO3 feature toggle.
     *
     * @param   string  $featureName  The name of the feature you want to disable
     *
     * @return $this
     * @see enableFeature
     */
    public function disableFeature(string $featureName): self
    {
        $this->featureToggles[$featureName] = false;
        
        return $this;
    }
    
    /**
     * Internal helper to store the configuration on the config state
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $state
     */
    public function finish(ConfigState $state): void
    {
        $state->mergeIntoArray('TYPO3_CONF_VARS.SYS.Objects', $this->xClasses);
        $state->mergeIntoArray('TYPO3_CONF_VARS.SYS.caching.cacheConfigurations', $this->cacheConfigurations);
        $state->mergeIntoArray('TYPO3_CONF_VARS.SYS.features', array_merge(
            $this->featureToggleDefaults, $this->featureToggles
        ));
        $state->mergeIntoArray('TYPO3_CONF_VARS.LOG', array_reduce(
            $this->logConfigurations,
            static function (array $target, array $item) {
                return Arrays::merge($target, $item);
            },
            []));
    }
}
