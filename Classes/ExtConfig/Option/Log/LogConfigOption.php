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
 * Last modified: 2020.03.19 at 11:54
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\Log;

use LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption;
use LaborDigital\Typo3BetterApi\Log\BeLogWriter;
use LaborDigital\Typo3BetterApi\Log\BetterFileWriter;
use LaborDigital\Typo3BetterApi\Log\StreamWriter;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;

class LogConfigOption extends AbstractExtConfigOption
{

    /**
     * Registers a new logfile writer in the system. It utilizes our internal
     * better file writer that has built-in log rotation capabilities.
     *
     * @param   string  $name     A speaking name for your log -> used only for the file name generation
     * @param   array   $options  Additional log configuration options
     *                            - logLevel int: This is equivalent with one of the LogLevel constants.
     *                            Default:
     *                            -- LogLevel::INFO: if the TYPO3 context is set to "development",
     *                            a frontend request is executed and TYPO3_CONF_VARS.FE.debug is truthy,
     *                            or a backend/install/CLI request is executed and TYPO3_CONF_VARS.BE.debug is truthy,
     *                            -- LogLevel::ERROR in all other cases
     *                            - namespace string (Vendor/ExtKey): The PHP namespace for the logger to be active in.
     *                            This can be either a class name or a part of a php namespace. If an empty
     *                            string is given the configuration is applied globally
     *                            - global bool: If this flag is set the writer is set as a global "default",
     *                            this will disable the "namespace" and "processor" options, tho!
     *                            - processor array: the processor configuration array for the configured loglevel
     *                            - logRotation bool (TRUE): By default the log files will be rotated once a day.
     *                            If you want to disable the log rotation set this option to false.
     *                            - filesToKeep int (5): If logRotation is enabled, this defines how many
     *                            files will be kept before they are deleted
     *
     * @see \TYPO3\CMS\Core\Log\LogLevel
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Logging/Configuration/Index.html
     */
    public function registerFileLog(string $name, array $options = []): self
    {
        $options           = $this->prepareLogOptions($options, [
            'logRotation' => [
                'type'    => 'bool',
                'default' => true,
            ],
            'filesToKeep' => [
                'type'    => 'int',
                'default' => 5,
            ],
        ]);
        $options['writer'] = [
            BetterFileWriter::class => [
                'logRotation' => $options['logRotation'],
                'filesToKeep' => $options['filesToKeep'],
                'name'        => $name,
            ],
        ];
        $this->applyLogConfiguration($options);

        return $this;
    }

    /**
     * Registers a new stream logger the system. It works quite similar to the syslog writer that is available in the
     * TYPO3 core, but allows you to define the stream to write to. By default it will write to php://stdout which
     * means it is perfect for logging inside of docker containers.
     *
     * @param   array  $options   Additional log configuration options
     *                            - logLevel int: This is equivalent with one of the LogLevel constants.
     *                            Default:
     *                            -- LogLevel::INFO: if the TYPO3 context is set to "development",
     *                            a frontend request is executed and TYPO3_CONF_VARS.FE.debug is truthy,
     *                            or a backend/install/CLI request is executed and TYPO3_CONF_VARS.BE.debug is truthy,
     *                            -- LogLevel::ERROR in all other cases
     *                            - namespace string (Vendor/ExtKey): The PHP namespace for the logger to be active in.
     *                            This can be either a class name or a part of a php namespace. If an empty
     *                            string is given the configuration is applied globally
     *                            - global bool: If this flag is set the writer is set as a global "default",
     *                            this will disable the "namespace" and "processor" options, tho!
     *                            - processor array: the processor configuration array for the configured loglevel
     *                            - stream string (php://stdout): Allows you to configure the stream to write to.
     */
    public function registerStreamLogger(array $options): self
    {
        $options           = $this->prepareLogOptions($options, [
            'stream' => [
                'type'    => ['null', 'string'],
                'default' => null,
            ],
        ]);
        $options['writer'] = [
            StreamWriter::class => [
                'stream' => $options['stream'],
            ],
        ];
        $this->applyLogConfiguration($options);

        return $this;
    }

    /**
     * Registers a new backend log logger in the system. This type of logger is basically a hybrid of the
     * DatabaseWriter in the PSR-3 logging implementation, and the old-school $GLOBALS['BE_USER']->writelog() logger.
     * It writes the log entries always in the sys_log table, but fills the field sets of both implementations, while
     * doing so.
     *
     * @param   array  $options   Additional log configuration options
     *                            - logLevel int: This is equivalent with one of the LogLevel constants.
     *                            Default:
     *                            -- LogLevel::INFO: if the TYPO3 context is set to "development",
     *                            a frontend request is executed and TYPO3_CONF_VARS.FE.debug is truthy,
     *                            or a backend/install/CLI request is executed and TYPO3_CONF_VARS.BE.debug is truthy,
     *                            -- LogLevel::ERROR in all other cases
     *                            - namespace string (Vendor/ExtKey): The PHP namespace for the logger to be active in.
     *                            This can be either a class name or a part of a php namespace. If an empty
     *                            string is given the configuration is applied globally
     *                            - global bool: If this flag is set the writer is set as a global "default",
     *                            this will disable the "namespace" and "processor" options, tho!
     *                            - processor array: the processor configuration array for the configured loglevel
     */
    public function registerBeLogLogger(array $options): self
    {
        $options           = $this->prepareLogOptions($options);
        $options['writer'] = [
            BeLogWriter::class => [],
        ];
        $this->applyLogConfiguration($options);

        return $this;
    }

    /**
     * Registers any kind of log configuration based on your input.
     *
     * @param   array  $options  The options for your log configuration
     *                           - logLevel int (7|3): This is equivalent with one of the LogLevel constants.
     *                           Default:
     *                           -- LogLevel::INFO: if the TYPO3 context is set to "development",
     *                           a frontend request is executed and TYPO3_CONF_VARS.FE.debug is truthy,
     *                           or a backend/install/CLI request is executed and TYPO3_CONF_VARS.BE.debug is truthy,
     *                           -- LogLevel::ERROR in all other cases
     *                           - namespace string (Vendor/ExtKey): The PHP namespace for the logger to be active in.
     *                           This can be either a class name or a part of a php namespace. If an empty
     *                           string is given the configuration is applied globally
     *                           - global bool: If this flag is set the writer is set as a global "default",
     *                           this will disable the "namespace" and "processor" options, tho!
     *                           - writer array: the writer configuration array for the configured loglevel
     *                           - processor array: the processor configuration array for the configured loglevel
     *
     * @see \TYPO3\CMS\Core\Log\LogLevel
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Logging/Configuration/Index.html
     */
    public function registerLogConfig(array $options): self
    {
        $options = $this->prepareLogOptions($options);
        $this->applyLogConfiguration($options);

        return $this;
    }

    /**
     * Internal helper to build the options array based on the given input.
     * Allows to add additional config definitions to be used for different log type
     *
     * @param   array  $options
     * @param   array  $additionalDefinition
     *
     * @return array
     */
    protected function prepareLogOptions(array $options, array $additionalDefinition = []): array
    {
        $env     = $this->context->TypoContext->Env();
        $isDebug = $env->isDev() || $env->isFrontend() && $env->isFeDebug()
                   || ! $env->isFrontend()
                      && $env->isBeDebug();

        return Options::make($options, Arrays::merge([
            'logLevel'  => [
                'type'    => ['string', 'int'],
                'default' => $isDebug ? LogLevel::INFO : LogLevel::ERROR,
                'values'  => [
                    LogLevel::EMERGENCY,
                    LogLevel::ERROR,
                    LogLevel::CRITICAL,
                    LogLevel::WARNING,
                    LogLevel::NOTICE,
                    LogLevel::INFO,
                    LogLevel::DEBUG,
                    LogLevel::ALERT,
                ],
            ],
            'namespace' => [
                'type'    => 'string',
                'default' => function () {
                    return implode(
                        '\\',
                        array_filter([
                            Inflector::toCamelCase($this->context->getVendor()),
                            Inflector::toCamelCase($this->context->getExtKey()),
                        ])
                    );
                },
                'filter'  => static function ($v) {
                    return array_filter(explode('\\', $v));
                },
            ],
            'writer'    => [
                'type'    => 'array',
                'default' => [],
            ],
            'processor' => [
                'type'    => 'array',
                'default' => [],
            ],
            'global'    => [
                'type'    => 'bool',
                'default' => false,
            ],
        ], $additionalDefinition));
    }

    /**
     * Injects the options into the globals super array
     *
     * @param   array  $options
     */
    protected function applyLogConfiguration(array $options): void
    {
        if ($options['global']) {
            $GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'][$options['logLevel']][key($options['writer'])]
                = reset($options['writer']);

            $this->flushLogManager('');
        } else {
            $config = [
                'writerConfiguration'    => [
                    $options['logLevel'] => $options['writer'],
                ],
                'processorConfiguration' => [
                    $options['logLevel'] => $options['processor'],
                ],
            ];

            $path = Arrays::mergePaths(['LOG'], $options['namespace']);
            $temp = Arrays::setPath([], $path, $config);

            $GLOBALS['TYPO3_CONF_VARS'] = Arrays::merge($GLOBALS['TYPO3_CONF_VARS'], $temp);
            $this->flushLogManager(implode('.', $options['namespace']));
        }
    }

    /**
     * Helper to reset the log manager when we updated the log config
     * NOTE: This is a temporary workaround and should be fixed in v10
     *
     * @param   string|null  $loggerName
     *
     * @deprecated
     */
    protected function flushLogManager(?string $loggerName = null): void
    {
        $manager = $this->context->getInstanceOf(LogManager::class);
        if ($loggerName) {
            $manager->registerLogger($loggerName);
        } else {
            $manager->reset();
        }
    }
}
