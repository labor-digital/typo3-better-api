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
 * Last modified: 2020.03.19 at 01:21
 */

namespace LaborDigital\T3ba\Tool\Tsfe;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\Simulation\EnvironmentSimulator;
use LaborDigital\T3ba\Tool\Simulation\SimulatedTypoScriptFrontendController;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class TsfeService implements SingletonInterface, PublicServiceInterface
{
    use ContainerAwareTrait;
    use TypoContextAwareTrait;
    
    /**
     * Returns true if the frontend is being simulated
     *
     * @return bool
     * @see EnvironmentSimulator::runWithEnvironment()
     */
    public function isSimulated(): bool
    {
        return $this->hasTsfe() && $this->getTsfe() instanceof SimulatedTypoScriptFrontendController;
    }
    
    /**
     * Returns true if the system has a typoScript frontend controller instance
     *
     * @return bool
     */
    public function hasTsfe(): bool
    {
        return isset($GLOBALS['TSFE']) && $GLOBALS['TSFE'] instanceof TypoScriptFrontendController
               && ! empty($GLOBALS['TSFE']->cObj);
    }
    
    /**
     * This method is used to ALWAYS return an instance of the typoScript frontend controller.
     * If we have to forcefully initialize it, we will do that.
     *
     * @return TypoScriptFrontendController
     * @throws \LaborDigital\T3ba\Tool\Tsfe\TsfeNotLoadedException
     */
    public function getTsfe(): TypoScriptFrontendController
    {
        // Check if the tsfe is initialized
        if ($this->hasTsfe()) {
            return $GLOBALS['TSFE'];
        }
        throw new TsfeNotLoadedException('The TypoScript frontend controller is not loaded!');
    }
    
    /**
     * Returns a prepared content object renderer instance.
     * If this method is used in the backend / in cli context
     * the frontend object might not be fully initialized.
     *
     * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    public function getContentObjectRenderer(): ContentObjectRenderer
    {
        $cObj = null;
        
        // Get the content object renderer from the frontend
        if ($this->hasTsfe()) {
            $cObj = $this->getTsfe()->cObj;
        }
        
        // Get the content object renderer from the config manager
        if (! $cObj instanceof ContentObjectRenderer && $this->getTypoContext()->env()->isFrontend()) {
            $cObj = $this->getService(ConfigurationManager::class)->getContentObject();
        }
        
        // Create it ourselves
        if (! $cObj instanceof ContentObjectRenderer) {
            return $this->getService(EnvironmentSimulator::class)->runWithEnvironment([], function () {
                return $this->getTsfe()->cObj;
            });
        }
        
        // Done
        return $cObj;
    }
}
