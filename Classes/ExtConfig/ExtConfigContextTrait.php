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
 * Last modified: 2020.10.19 at 15:03
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig;


use Neunerlei\Configuration\Handler\HandlerConfigurator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait ExtConfigContextTrait
{

    /**
     * The configuration context we are working with
     *
     * @var \LaborDigital\T3BA\ExtConfig\ExtConfigContext
     */
    protected $context;

    /**
     * Similar to getInstance() but does not create the instance using the container, but the general utility
     *
     * @param   string  $class            The class to instantiate
     * @param   array   $constructorArgs  A optional list of arguments to pass to the constructor
     *
     * @return object|\Psr\Log\LoggerAwareInterface|string|\TYPO3\CMS\Core\SingletonInterface
     */
    protected function getInstanceWithoutDi(string $class, array $constructorArgs = [])
    {
        return GeneralUtility::makeInstance($class, ...$constructorArgs);
    }

    /**
     * Registers the default ext config location for your handler.
     * Just a helper, to save a bit of typing.
     *
     * @param   \Neunerlei\Configuration\Handler\HandlerConfigurator  $configurator
     */
    protected function registerDefaultLocation(HandlerConfigurator $configurator): void
    {
        $configurator->registerLocation('Configuration/ExtConfig');
    }
}
