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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Tool\Simulation\Pass;


use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Tool\Page\PageService;
use LaborDigital\T3BA\Tool\Simulation\SimulatedTypoScriptFrontendController;
use LaborDigital\T3BA\Tool\Tsfe\TsfeService;
use LaborDigital\T3BA\Tool\TypoContext\TypoContextAwareTrait;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class TsfeSimulationPass implements SimulatorPassInterface
{
    use ContainerAwareTrait;
    use TypoContextAwareTrait;
    
    /**
     * @var TsfeService
     */
    protected $tsfeService;
    
    /**
     * @var PageService
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
     * @param   TsfeService  $tsfeService
     * @param   PageService  $pageService
     */
    public function __construct(TsfeService $tsfeService, PageService $pageService)
    {
        $this->tsfeService = $tsfeService;
        $this->pageService = $pageService;
    }
    
    /**
     * @inheritDoc
     */
    public function addOptionDefinition(array $options): array
    {
        $options['bootTsfe'] = [
            'type' => 'bool',
            'default' => true,
        ];
        $options['pid'] = [
            'type' => ['int', 'null'],
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
                   ! $this->getService(TsfeService::class)->hasTsfe()
                   || (
                       $options['pid'] !== null
                       && $this->getTypoContext()->pid()->getCurrent() !== $options['pid']
                   )
               );
    }
    
    /**
     * @inheritDoc
     */
    public function setup(array $options, array &$storage): void
    {
        // Backup the tsfe
        $storage['tsfe'] = $GLOBALS['TSFE'];
        $storage['languageAspect'] = clone $this->getTypoContext()->getRootContext()->getAspect('language');
        $storage['pid'] = $this->getTypoContext()->config()->getConfigState()->get('t3ba.pids', []);
        
        // Store the language aspect temporarily
        $pid = $options['pid'] ?? $this->getTypoContext()->pid()->getCurrent();
        $GLOBALS['TSFE'] = $this->makeSimulatedTsfe($pid, $storage);
        
        // Make sure the language aspect stays the same way as we set it...
        $this->getTypoContext()->getRootContext()->setAspect('language', $storage['languageAspect']);
    }
    
    /**
     * @inheritDoc
     */
    public function rollBack(array $storage): void
    {
        $this->getTypoContext()->config()->getConfigState()->set('t3ba.pids', $storage['pid']);
        $GLOBALS['TSFE'] = $storage['tsfe'];
        $this->getTypoContext()->getRootContext()->setAspect('language', $storage['languageAspect']);
    }
    
    /**
     * Internal helper that is used to create a new tsfe instance
     *
     * It is not fully initialized and also not available on $GLOBALS['TSFE'],
     * but should do the trick for most of your needs
     *
     * @param   int    $pid  The pid to create the controller instance with
     * @param   array  $storage
     *
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function makeSimulatedTsfe(int $pid, array $storage): TypoScriptFrontendController
    {
        $key = md5(serialize($storage['languageAspect']) . '_' . $pid);
        if (isset($this->instanceCache[$key])) {
            return $this->instanceCache[$key];
        }
        
        $controller = $this->makeInstance(
            SimulatedTypoScriptFrontendController::class, [
                null,
                $pid,
                $this->getTypoContext()->language()->getCurrentFrontendLanguage(),
            ]
        );
        $GLOBALS['TSFE'] = $controller;
        $controller->sys_page = $this->pageService->getPageRepository();
        $controller->rootLine = $this->pageService->getRootLine($pid);
        $controller->page = $this->pageService->getPageInfo($pid);
        $controller->getConfigArray();
        $controller->settingLanguage();
        $controller->cObj = $this->makeInstance(ContentObjectRenderer::class, [$controller, $this->getContainer()]);
        $controller->fe_user = $this->makeInstance(FrontendUserAuthentication::class);
        
        
        return $this->instanceCache[$key] = $controller;
    }
}
