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
 * Last modified: 2021.12.08 at 13:35
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Core\Override;


use TYPO3\CMS\Core\Utility\T3BaCopyRootlineUtility;

/**
 * I don't really extend the root line utility here,
 * however the {@link \LaborDigital\T3ba\Tool\Page\ExtendedRootLineUtility} needs this in order to
 * call itself recursively without copying the whole code of generateRootlineCache()
 */
class ExtendedRootLineUtility extends T3BaCopyRootlineUtility
{
    
}