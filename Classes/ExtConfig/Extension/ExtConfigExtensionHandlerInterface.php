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
 * Last modified: 2020.03.21 at 16:20
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\ExtConfig\Extension;

use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;

interface ExtConfigExtensionHandlerInterface
{
    
    /**
     * ExtConfigExtensionHandlerInterface constructor.
     *
     * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext $context
     */
    public function __construct(ExtConfigContext $context);
    
    /**
     * Applied after the ext config service gathered all extensions.
     * Should be used to perform all required actions to apply the extensions.
     *
     * @param array $extensions
     */
    public function generate(array $extensions): void;
}
