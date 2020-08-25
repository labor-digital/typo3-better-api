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
 * Last modified: 2020.03.18 at 17:49
 */

namespace LaborDigital\Typo3BetterApi\Translation;

use LaborDigital\Typo3BetterApi\Container\TypoContainer;

class TranslationLabelProvider
{
    
    /**
     * Stores all requested labels to speed up subsequent requests
     *
     * @var array
     */
    protected static $labelCache = [];
    
    
    /**
     * @var \LaborDigital\Typo3BetterApi\Translation\TranslationService
     */
    protected static $translator;
    
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
        $input = trim($input);
        
        // Skip events and override resolution if we already resolved this label
        if (isset(static::$labelCache[$input])) {
            return static::$labelCache[$input] === true ?
                $translationProvider($input) : $translationProvider(static::$labelCache[$input]);
        }
        
        // Resolve our label
        $inputRaw                   = $input;
        $input                      = static::getTranslator()->getTranslationKeyMaybe($input);
        static::$labelCache[$input] = $inputRaw === $input ? true : $input;
        
        // Do the translation
        return $translationProvider($input);
    }
    
    /**
     * Returns the translator instance
     *
     * @return \LaborDigital\Typo3BetterApi\Translation\TranslationService
     */
    protected static function getTranslator(): TranslationService
    {
        if (! empty(static::$translator)) {
            return static::$translator;
        }
        
        return static::$translator = TypoContainer::getInstance()->get(TranslationService::class);
    }
}
