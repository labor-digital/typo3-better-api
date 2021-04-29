<?php
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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Io;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class GeneralUtilityAdapter extends GeneralUtility
{
    
    /**
     * Use the xml2array method without the need for a database connection...
     *
     * @param           $string
     * @param   string  $NSprefix
     * @param   bool    $reportDocTag
     *
     * @return mixed
     * @see GeneralUtility::xml2array()
     */
    public static function xml2arrayWithoutCache($string, $NSprefix = '', $reportDocTag = false)
    {
        return GeneralUtility::xml2arrayProcess($string, $NSprefix, $reportDocTag);
    }
}
