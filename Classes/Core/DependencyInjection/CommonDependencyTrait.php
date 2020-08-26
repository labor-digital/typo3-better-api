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

use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
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

    /**
     * Returns the typo context instance
     *
     * @return TypoContext
     */
    protected function TypoContext(): TypoContext
    {
        return $this->getSingletonOf(TypoContext::class);
    }
//
//    /**
//     * Returns the db service instance
//     *
//     * @return \LaborDigital\Typo3BetterApi\Domain\DbService\DbServiceInterface
//     */
//    protected function Db(): DbServiceInterface
//    {
//        return $this->getSingletonOf(DbServiceInterface::class);
//    }
//
//    /**
//     * Returns the link service instance
//     *
//     * @return \LaborDigital\Typo3BetterApi\Link\LinkService
//     */
//    protected function Links(): LinkService
//    {
//        return $this->getSingletonOf(LinkService::class);
//    }
//
//    /**
//     * Returns the TSFE service instance
//     *
//     * @return \LaborDigital\Typo3BetterApi\Tsfe\TsfeService
//     */
//    protected function Tsfe(): TsfeService
//    {
//        return $this->getSingletonOf(TsfeService::class);
//    }
//
//    /**
//     * Returns the page service instance
//     *
//     * @return \LaborDigital\Typo3BetterApi\Page\PageService
//     */
//    protected function Page(): PageService
//    {
//        return $this->getSingletonOf(PageService::class);
//    }
//
//    /**
//     * Returns the fal file service instance
//     *
//     * @return \LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService
//     */
//    protected function FalFiles(): FalFileService
//    {
//        return $this->getSingletonOf(FalFileService::class);
//    }
//
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
//
//    /**
//     * Returns the translation service instance
//     *
//     * @return \LaborDigital\Typo3BetterApi\Translation\TranslationService
//     */
//    protected function Translation(): TranslationService
//    {
//        return $this->getSingletonOf(TranslationService::class);
//    }
//
//    /**
//     * Returns the environment simulator instance
//     *
//     * @return \LaborDigital\Typo3BetterApi\Simulation\EnvironmentSimulator
//     */
//    protected function Simulator(): EnvironmentSimulator
//    {
//        return $this->getInstanceOf(EnvironmentSimulator::class);
//    }
//
//    /**
//     * Returns the Frontend session provider instance
//     *
//     * @return \LaborDigital\Typo3BetterApi\Session\FrontendSessionProvider
//     */
//    protected function FrontendSession(): FrontendSessionProvider
//    {
//        return $this->getSingletonOf(SessionService::class)->getFrontendSession();
//    }
//
//    /**
//     * Returns the Backend session provider instance
//     *
//     * @return \LaborDigital\Typo3BetterApi\Session\BackendSessionProvider
//     */
//    protected function BackendSession(): BackendSessionProvider
//    {
//        return $this->getSingletonOf(SessionService::class)->getBackendSession();
//    }
}
