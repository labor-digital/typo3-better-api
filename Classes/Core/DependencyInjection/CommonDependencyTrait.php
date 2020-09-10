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
 * Last modified: 2020.08.23 at 23:23
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
 * Last modified: 2020.05.12 at 18:01
 */


namespace LaborDigital\T3BA\Core\DependencyInjection;

use LaborDigital\T3BA\Tool\Database\DbService;
use LaborDigital\T3BA\Tool\DataHandler\DataHandlerService;
use LaborDigital\T3BA\Tool\Fal\FalService;
use LaborDigital\T3BA\Tool\Link\LinkService;
use LaborDigital\T3BA\Tool\Page\PageService;
use LaborDigital\T3BA\Tool\Session\SessionService;
use LaborDigital\T3BA\Tool\Simulation\EnvironmentSimulator;
use LaborDigital\T3BA\Tool\Translation\Translator;
use LaborDigital\T3BA\Tool\Tsfe\TsfeService;
use LaborDigital\T3BA\Tool\TypoContext\TypoContextAwareTrait;
use LaborDigital\T3BA\Tool\TypoScript\TypoScriptService;
use Neunerlei\EventBus\EventBusInterface;

/**
 * Trait CommonServiceDependencyTrait
 *
 * An extension for the LazyServiceDependencyTrait to provide shortcuts to frequently used service classes.
 *
 * This is designed for abstract controllers or entities and should not be used in other circumstances -> Keep your
 * code free from hidden dependencies
 *
 * @package LaborDigital\Typo3BetterApi\Container
 */
trait CommonDependencyTrait
{
    use ContainerAwareTrait;
    use TypoContextAwareTrait;

    /**
     * Returns the db service instance
     *
     * @return \LaborDigital\T3BA\Tool\Database\DbService
     */
    protected function Db(): DbService
    {
        return $this->getSingletonOf(DbService::class);
    }

    /**
     * Returns the link service instance
     *
     * @return \LaborDigital\T3BA\Tool\Link\LinkService
     */
    protected function Links(): LinkService
    {
        return $this->getSingletonOf(LinkService::class);
    }

    /**
     * Returns the TSFE service instance
     *
     * @return \LaborDigital\T3BA\Tool\Tsfe\TsfeService
     */
    protected function Tsfe(): TsfeService
    {
        return $this->getSingletonOf(TsfeService::class);
    }

    /**
     * Returns the page service instance
     *
     * @return PageService
     */
    protected function Page(): PageService
    {
        return $this->getSingletonOf(PageService::class);
    }

    /**
     * Returns the fal file service instance
     *
     * @return \LaborDigital\T3BA\Tool\Fal\FalService
     */
    protected function Fal(): FalService
    {
        return $this->getSingletonOf(FalService::class);
    }

    /**
     * Returns the event bus instance
     *
     * @return \Neunerlei\EventBus\EventBusInterface
     */
    protected function EventBus(): EventBusInterface
    {
        return $this->getSingletonOf(EventBusInterface::class);
    }

    /**
     * Returns the typo script service instance
     *
     * @return \LaborDigital\T3BA\Tool\TypoScript\TypoScriptService
     */
    protected function TypoScript(): TypoScriptService
    {
        return $this->getSingletonOf(TypoScriptService::class);
    }

    /**
     * Returns the translation service instance
     *
     * @return \LaborDigital\T3BA\Tool\Translation\Translator
     */
    protected function Translator(): Translator
    {
        return $this->getSingletonOf(Translator::class);
    }

    /**
     * Returns the environment simulator instance
     *
     * @return \LaborDigital\T3BA\Tool\Simulation\EnvironmentSimulator
     */
    protected function Simulator(): EnvironmentSimulator
    {
        return $this->getInstanceOf(EnvironmentSimulator::class);
    }

    /**
     * Returns the session abstraction provider instance
     *
     * @return \LaborDigital\T3BA\Tool\Session\SessionService
     */
    protected function Session(): SessionService
    {
        return $this->getSingletonOf(SessionService::class);
    }

    /**
     * Returns the instance of the data handler abstraction service class
     *
     * @return \LaborDigital\T3BA\Tool\DataHandler\DataHandlerService
     */
    protected function DataHandler(): DataHandlerService
    {
        return $this->getSingletonOf(DataHandlerService::class);
    }
}
