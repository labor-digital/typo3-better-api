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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);

namespace LaborDigital\T3ba\Tool\Simulation;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class SimulatedTypoScriptFrontendController
 *
 * The internal frontend controller to load instead of the default one if the simulator creates a new frontend instance
 *
 * @package LaborDigital\T3ba\Tool\Simulation
 */
class SimulatedTypoScriptFrontendController extends TypoScriptFrontendController implements NoDiInterface
{
    /**
     * @inheritDoc
     */
    public function settingLanguage(ServerRequestInterface $request = null): void
    {
        // Make sure to use the existing language service if possible
        if (! isset($GLOBALS['LANG'])) {
            parent::settingLanguage();
        }
        
        // Link the language into the existing service
        /** @var \TYPO3\CMS\Core\Localization\LanguageService $lang */
        $lang = $GLOBALS['LANG'];
        $this->config['config']['language'] = $lang->lang;
        
        parent::settingLanguage($request);
    }
}
