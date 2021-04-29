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
 * Last modified: 2021.01.13 at 17:10
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Core;


use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Log\LogLevel;

trait LogConfigTrait
{
    
    /**
     * The list of registered log configurations
     *
     * @var array
     */
    protected $logConfigurations = [];
    
    /**
     * Internal helper to build the options array based on the given input.
     * Allows to add additional config definitions to be used for different log type.
     *
     * @param   array  $options
     * @param   array  $additionalDefinition
     *
     * @return array
     */
    protected function prepareLogConfig(array $options, array $additionalDefinition = []): array
    {
        return Options::make($options, Arrays::merge([
            'key' => [
                'type' => 'string',
                'default' => (string)count($this->logConfigurations),
            ],
            'logLevel' => [
                'type' => ['string', 'int'],
                'default' => $this->getTypoContext()->Env()->isDev() ? LogLevel::DEBUG : LogLevel::ERROR,
                'values' => [
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
                'type' => 'string',
                'default' => function () {
                    return implode(
                        '\\',
                        array_filter([
                            Inflector::toCamelCase($this->context->getVendor()),
                            Inflector::toCamelCase($this->context->getExtKey()),
                        ])
                    );
                },
                'filter' => static function ($v) {
                    return array_filter(explode('\\', $v));
                },
            ],
            'writer' => [
                'type' => 'array',
                'default' => [],
            ],
            'processor' => [
                'type' => 'array',
                'default' => [],
            ],
        ], $additionalDefinition));
    }
    
    /**
     * Adds the log config option to the stack
     *
     * @param   array  $options
     *
     * @return $this
     */
    protected function pushLogConfig(array $options)
    {
        $config = [
            'writerConfiguration' => [
                $options['logLevel'] => $options['writer'],
            ],
            'processorConfiguration' => [
                $options['logLevel'] => $options['processor'],
            ],
        ];
        
        $this->logConfigurations[$options['key']] = Arrays::setPath([], $options['namespace'], $config);
        
        return $this;
    }
    
}
