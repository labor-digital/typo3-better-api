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
 * Last modified: 2020.08.23 at 23:23
 */

namespace LaborDigital\T3BA\Tool\Translation;

use LaborDigital\T3BA\Core\Di\StaticContainerAwareTrait;

class TranslationLabelProvider
{
    use StaticContainerAwareTrait;
    
    /**
     * Stores all requested labels to speed up subsequent requests
     *
     * @var array
     */
    protected static $labelCache = [];
    
    /**
     * Bridge for the translation service, used in the core modding classes
     *
     * @param             $input
     * @param   callable  $translationProvider
     *
     * @return mixed
     */
    public static function provideLabelFor($input, callable $translationProvider)
    {
        // Ignore if the input is not a string
        if (! is_string($input)) {
            return $translationProvider($input);
        }
        
        // Skip events and override resolution if we already resolved this label
        $input = trim($input);
        if (isset(static::$labelCache[$input])) {
            return static::$labelCache[$input] === true ?
                $translationProvider($input) : $translationProvider(static::$labelCache[$input]);
        }
        
        // Resolve our label
        $inputRaw = $input;
        $input = static::getService(Translator::class)->getLabelKey($input);
        static::$labelCache[$input] = $inputRaw === $input ? true : $input;
        
        // Do the translation
        return $translationProvider($input);
    }
}
