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
 * Last modified: 2021.01.13 at 18:57
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig\Traits;


use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Neunerlei\Configuration\Util\ConfigContextAwareInterface;
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
     * @return mixed
     */
    protected function getInstanceWithoutDi(string $class, array $constructorArgs = [])
    {
        $i = GeneralUtility::makeInstance($class, ...$constructorArgs);

        if (isset($this->context) && $i instanceof ConfigContextAwareInterface) {
            $i->setConfigContext($this->context);
        }

        return $i;
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
