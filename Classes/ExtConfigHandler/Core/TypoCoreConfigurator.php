<?php
/*
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
 * Last modified: 2020.08.24 at 21:19
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Core;


use LaborDigital\T3BA\ExtConfig\Abstracts\AbstractExtConfigConfigurator;
use LaborDigital\T3BA\Tool\Log\BetterFileWriter;
use LaborDigital\T3BA\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\Options\Options;

class TypoCoreConfigurator extends AbstractExtConfigConfigurator
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
     * Registers a xClass override for a given class
     *
     * @param   string  $classToOverride      The class to override with the xClass
     * @param   string  $classToOverrideWith  The class to use as a xClass
     *
     * @return \LaborDigital\T3BA\ExtConfigHandler\Core\TypoCoreConfigurator
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
     * Registers a new cache configuration to TYPO3's caching framework.
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
     * @return \LaborDigital\T3BA\ExtConfigHandler\Core\TypoCoreConfigurator
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
     * Registers a new logfile writer in the system. It utilizes our internal
     * better file writer that has built-in log rotation capabilities.
     *
     * @param   array  $options   Additional log configuration options
     *                            - key string: Allows you to provide a unique key for this configuration
     *                            so other configuration classes may override your config. The key will also be used
     *                            for the file name generation
     *                            - logLevel int (7|3): This is equivalent with one of the LogLevel constants.
     *                            It defines the minimal viable severity that should be logged, all levels with a higher
     *                            number that the given level will be be ignored
     *                            - namespace string (Vendor/ExtKey): The PHP namespace for the logger to be active in.
     *                            This can be either a class name or a part of a php namespace. If an empty
     *                            string is given the configuration is applied globally
     *                            - writer array: the writer configuration array for the configured loglevel
     *                            - processor array: the processor configuration array for the configured loglevel
     *                            - logRotation bool (TRUE): By default the log files will be rotated once a day.
     *                            If you want to disable the log rotation set this option to false.
     *                            - filesToKeep int (5): If logRotation is enabled, this defines how many
     *                            files will be kept before they are deleted
     *
     * @return \LaborDigital\T3BA\ExtConfigHandler\Core\TypoCoreConfigurator
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Logging/Configuration/Index.html
     * @see \TYPO3\CMS\Core\Log\LogLevel
     */
    public function registerFileLog(array $options = []): self
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
     * @return \LaborDigital\T3BA\ExtConfigHandler\Core\TypoCoreConfigurator
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Logging/Configuration/Index.html
     * @see \TYPO3\CMS\Core\Log\LogLevel
     */
    public function registerLogWriter(array $options): self
    {
        return $this->pushLogConfig($this->prepareLogConfig($options));
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
        $state->mergeIntoArray('TYPO3_CONF_VARS.LOG', array_reduce(
            $this->logConfigurations,
            static function (array $target, array $item) {
                return Arrays::merge($target, $item);
            },
            []));
    }
}
