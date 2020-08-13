<?php
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
 * Last modified: 2020.07.16 at 20:32
 */

declare(strict_types=1);


namespace LaborDigital\Typo3BetterApi\Simulation\Pass;


use LaborDigital\Typo3BetterApi\Container\CommonDependencyTrait;
use LaborDigital\Typo3BetterApi\Simulation\SimulatedTypoScriptFrontendController;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

class TsfeSimulationPass implements SimulatorPassInterface
{
    use CommonDependencyTrait;

    protected $tsfeBackup;
    protected $languageAspectBackup;

    /**
     * @inheritDoc
     */
    public function __construct() { }

    /**
     * @inheritDoc
     */
    public function addOptionDefinition(array $options): array
    {
        $options['bootTsfe'] = [
            'type'    => 'bool',
            'default' => true,
        ];
        $options['pid']      = [
            'type'    => ['int', 'null'],
            'default' => null,
        ];

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function requireSimulation(array $options): bool
    {
        return $options['bootTsfe']
               || (
                   ! $this->Tsfe()->hasTsfe()
                   || (
                       $options['pid'] !== null
                       && $this->TypoContext()->Pid()->getCurrent() !== $options['pid']
                   )
               );
    }

    /**
     * @inheritDoc
     */
    public function setup(array $options): void
    {
        // Backup the tsfe
        $this->tsfeBackup           = $GLOBALS['TSFE'];
        $this->languageAspectBackup = clone $this->TypoContext()->getRootContext()->getAspect('language');

        // Store the language aspect temporarily
        $pid = $options['pid'] ?? $this->TypoContext()->Pid()->getCurrent();
        $this->makeSimulatedTsfe($pid);

        // Make sure the language aspect stays the same way as we set it...
        $this->TypoContext()->getRootContext()->setAspect('language', $this->languageAspectBackup);
    }

    /**
     * @inheritDoc
     */
    public function rollBack(): void
    {
        $GLOBALS['TSFE'] = $this->tsfeBackup;
        $this->TypoContext()->getRootContext()->setAspect('language', $this->languageAspectBackup);
    }


    /**
     * Internal helper that is used to create a new tsfe instance
     *
     * It is not fully initialized and also not available on $GLOBALS['TSFE'],
     * but should do the trick for most of your needs
     *
     * @param   int  $pid  The pid to create the controller instance with
     *
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function makeSimulatedTsfe(int $pid): TypoScriptFrontendController
    {
        $controller           = $this->getInstanceOf(SimulatedTypoScriptFrontendController::class, [null, $pid, 0,]);
        $GLOBALS['TSFE']      = $controller;
        $controller->sys_page = $this->getInstanceOf(PageRepository::class);
        $controller->rootLine = $this->getInstanceOf(RootlineUtility::class, [$pid])->get();
        $controller->page     = $this->Page()->getPageInfo($pid);
        $controller->getConfigArray();
        $controller->settingLanguage();
        $controller->settingLocale();
        $controller->cObj    = $this->getInstanceOf(ContentObjectRenderer::class, [$controller]);
        $controller->fe_user = $this->getInstanceOf(FrontendUserAuthentication::class);

        return $controller;
    }
}
