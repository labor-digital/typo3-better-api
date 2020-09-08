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
 * Last modified: 2020.08.13 at 23:32
 */

declare(strict_types=1);


namespace LaborDigital\Typo3BetterApi\Simulation;


use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class SimulatedTypoScriptFrontendController
 *
 * The internal frontend controller to load instead of the default one if the simulator creates a new frontend instance
 *
 * @package LaborDigital\Typo3BetterApi\Simulation
 */
class SimulatedTypoScriptFrontendController extends TypoScriptFrontendController
{
    /**
     * @inheritDoc
     */
    public function settingLanguage()
    {
        // Make sure to use the existing language service if possible
        if (! isset($GLOBALS['LANG'])) {
            return parent::settingLanguage();
        }

        // Link the language into the existing service
        /** @var \TYPO3\CMS\Core\Localization\LanguageService $lang */
        $lang                               = $GLOBALS['LANG'];
        $this->config['config']['language'] = $lang->lang;

        return parent::settingLanguage();
    }


}