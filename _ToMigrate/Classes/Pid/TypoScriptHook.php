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
 * Last modified: 2020.03.19 at 01:40
 */

namespace LaborDigital\Typo3BetterApi\Pid;

use LaborDigital\Typo3BetterApi\BetterApiException;
use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\SingletonInterface;

class TypoScriptHook implements SingletonInterface
{
    use CommonServiceLocatorTrait;
    
    /**
     * This hook is called when the TSFE parses the typoScript setup.
     * It is used to extract the changed pid mapping and injects the changes back into the pid service.
     *
     * @param   array  $config
     *
     * @throws \LaborDigital\Typo3BetterApi\BetterApiException
     */
    public function updatePidService(array $config)
    {
        $pidConfig   = Arrays::getPath($config, ['config', 'betterApi.', 'pid.'], []);
        $pidConfig   = $this->TypoScript->removeDots($pidConfig);
        $pidConfig   = Arrays::flatten($pidConfig);
        $pidAspect   = $this->TypoContext->getPidAspect();
        $pids        = Arrays::flatten($pidAspect->getAllPids());
        $pidsChanged = array_diff_assoc($pidConfig, $pids);
        foreach ($pidsChanged as $k => $v) {
            if (! is_numeric($v)) {
                throw new BetterApiException("Failed to read the pid configuration from TypoScript! The value of key: \"$k\" but: \"$v\"!");
            }
            $pidAspect->setPid($k, (int)$v);
        }
    }
}
