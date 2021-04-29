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
 * Last modified: 2020.03.18 at 17:49
 */

namespace LaborDigital\T3BA\Core\Override;


use LaborDigital\T3BA\Tool\Translation\TranslationLabelProvider;
use TYPO3\CMS\Extbase\Utility\T3BA__Copy__LocalizationUtility;

class ExtendedLocalizationUtility extends T3BA__Copy__LocalizationUtility
{
    /**
     * @inheritDoc
     */
    public static function translate(
        string $key,
        ?string $extensionName = null,
        array $arguments = null,
        string $languageKey = null,
        array $alternativeLanguageKeys = null
    ): ?string
    {
        return TranslationLabelProvider::provideLabelFor($key,
            static function ($input) use ($extensionName, $arguments, $languageKey, $alternativeLanguageKeys) {
                return parent::translate($input, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys);
            });
    }
    
}
