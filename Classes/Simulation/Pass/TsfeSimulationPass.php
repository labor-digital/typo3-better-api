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


use LaborDigital\Typo3BetterApi\Container\ContainerAwareTrait;
use LaborDigital\Typo3BetterApi\Page\PageService;
use LaborDigital\Typo3BetterApi\Simulation\SimulatedTypoScriptFrontendController;
use LaborDigital\Typo3BetterApi\Tsfe\TsfeService;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

class TsfeSimulationPass implements SimulatorPassInterface
{
    use ContainerAwareTrait;

    /**
     * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
     */
    protected $typoContext;

    /**
     * @var \LaborDigital\Typo3BetterApi\Tsfe\TsfeService
     */
    protected $tsfeService;

    /**
     * @var \LaborDigital\Typo3BetterApi\Page\PageService
     */
    protected $pageService;

    /**
     * A list of simulated tsfe instances by their id/language identifiers
     * We store this instances to speed up the simulation process by avoiding a lot of
     * db requests when the typoScript template is parsed.
     *
     * @var SimulatedTypoScriptFrontendController[]
     */
    protected $instanceCache = [];

    /**
     * TsfeSimulationPass constructor.
     *
     * @param   \LaborDigital\Typo3BetterApi\TypoContext\TypoContext  $typoContext
     * @param   \LaborDigital\Typo3BetterApi\Tsfe\TsfeService         $tsfeService
     * @param   \LaborDigital\Typo3BetterApi\Page\PageService         $pageService
     */
    public function __construct(TypoContext $typoContext, TsfeService $tsfeService, PageService $pageService)
    {
        $this->typoContext = $typoContext;
        $this->tsfeService = $tsfeService;
        $this->pageService = $pageService;
    }

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
    public function requireSimulation(array $options, array &$storage): bool
    {
        return $options['bootTsfe']
               || (
                   ! $this->tsfeService->hasTsfe()
                   || (
                       $options['pid'] !== null
                       && $this->typoContext->Pid()->getCurrent() !== $options['pid']
                   )
               );
    }

    /**
     * @inheritDoc
     */
    public function setup(array $options, array &$storage): void
    {
        // Backup the tsfe
        $storage['tsfe']           = $GLOBALS['TSFE'];
        $storage['languageAspect'] = clone $this->typoContext->getRootContext()->getAspect('language');

        // Store the language aspect temporarily
        $pid             = $options['pid'] ?? $this->typoContext->Pid()->getCurrent();
        $GLOBALS['TSFE'] = $this->makeSimulatedTsfe($pid, $storage);

        // Make sure the language aspect stays the same way as we set it...
        $this->typoContext->getRootContext()->setAspect('language', $storage['languageAspect']);
    }

    /**
     * @inheritDoc
     */
    public function rollBack(array $storage): void
    {
        $GLOBALS['TSFE'] = $storage['tsfe'];
        $this->typoContext->getRootContext()->setAspect('language', $storage['languageAspect']);
    }

    /**
     * Internal helper that is used to create a new tsfe instance
     *
     * It is not fully initialized and also not available on $GLOBALS['TSFE'],
     * but should do the trick for most of your needs
     *
     * @param   int    $pid      The pid to create the controller instance with
     * @param   array  $storage  The storage array
     *
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function makeSimulatedTsfe(int $pid, array $storage): TypoScriptFrontendController
    {
        $key = md5(serialize($storage['languageAspect']) . '_' . $pid);
        if (isset($this->instanceCache[$key])) {
            return $this->instanceCache[$key];
        }

        $controller           = $this->getInstanceOf(SimulatedTypoScriptFrontendController::class, [null, $pid, 0,]);
        $GLOBALS['TSFE']      = $controller;
        $controller->sys_page = $this->getInstanceOf(PageRepository::class);
        $controller->rootLine = $this->getInstanceOf(RootlineUtility::class, [$pid])->get();
        $controller->page     = $this->pageService->getPageInfo($pid);
        $controller->getConfigArray();
        $controller->settingLanguage();
        $controller->settingLocale();
        $controller->cObj    = $this->getInstanceOf(ContentObjectRenderer::class, [$controller]);
        $controller->fe_user = $this->getInstanceOf(FrontendUserAuthentication::class);

        return $this->instanceCache[$key] = $controller;
    }
}
