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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig;

use LaborDigital\Typo3BetterApi\ExtConfig\OptionList\ExtConfigOptionList;
use TYPO3\CMS\Core\SingletonInterface;

interface ExtConfigInterface extends SingletonInterface
{
    
    /**
     * This method is used to setup the configuration for your extension.
     * Use the $configurator object to see which options you current installation supports.
     *
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\OptionList\ExtConfigOptionList  $configurator
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext                $context
     *
     * @return void
     */
    public function configure(ExtConfigOptionList $configurator, ExtConfigContext $context);
}
