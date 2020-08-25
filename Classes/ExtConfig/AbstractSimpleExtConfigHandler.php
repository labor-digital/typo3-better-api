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
 * Last modified: 2020.08.25 at 12:45
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig;

/**
 * Class AbstractSimpleExtConfigHandler
 *
 * Use this for simple ext config handlers where you just
 *
 * @package LaborDigital\T3BA\ExtConfig
 */
abstract class AbstractSimpleExtConfigHandler extends AbstractExtConfigHandler
{
    /**
     * @var \LaborDigital\T3BA\ExtConfig\ExtConfigConfiguratorInterface
     */
    protected $configurator;

    /**
     * @inheritDoc
     * @throws \LaborDigital\T3BA\ExtConfig\InvalidConfiguratorException
     */
    public function prepare(): void
    {
        $this->configurator = $this->getInstance($this->getConfiguratorClass());
        if (! $this->configurator instanceof ExtConfigConfiguratorInterface) {
            throw new InvalidConfiguratorException(
                'The configurator class: ' . $this->getConfiguratorClass() . ' has to implement the '
                . ExtConfigConfiguratorInterface::class . ' interface if you use the '
                . __CLASS__ . ' as a handler!');
        }
    }

    /**
     * @inheritDoc
     */
    public function handle(string $class): void
    {
        call_user_func([$class, 'configure'], $this->configurator, $this->context);
    }

    /**
     * @inheritDoc
     */
    public function finish(): void
    {
        $this->context->getState()->useNamespace($this->getStateNamespace(), [$this->configurator, 'finish']);
    }

    /**
     * Must return the name of the configurator class to use
     * The class has to implement the ExtConfigConfiguratorInterface!
     *
     * @return string
     * @see \LaborDigital\T3BA\ExtConfig\ExtConfigConfiguratorInterface
     */
    abstract protected function getConfiguratorClass(): string;

    /**
     * Must return the namespace of the state storage for this configurator
     *
     * @return string
     */
    abstract protected function getStateNamespace(): string;
}
