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
 * Last modified: 2020.03.18 at 19:45
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\Pids;

use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;

class PidTypoScriptGenerator
{
    /**
     * This helper is used to convert a list of pid's into the typoScript setup and constant code
     *
     * @param   array  $pids  The result of PidService->getAll()
     *
     * @return array
     */
    public function generate(array $pids): array
    {
        // Build the typoscript
        $constantsTs = $ts = [];
        foreach (Arrays::flatten($pids) as $k => $pid) {
            $key           = 'config.betterApi.pid.' . $k;
            $ts[]          = $key . '={$' . $key . '}';
            $constantsTs[] = '#cat=betterApi/pid; type=int+; label=Page ID ' . Inflector::toHuman($k);
            $constantsTs[] = $key . '=' . $pid;
        }
        
        // Done
        return [implode(PHP_EOL, $ts), implode(PHP_EOL, $constantsTs)];
    }
}
