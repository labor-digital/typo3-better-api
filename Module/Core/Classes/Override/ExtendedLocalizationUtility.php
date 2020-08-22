<?php
/*
 * Copyright 2020 Martin Neundorfer (Neunerlei)
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
 * Last modified: 2020.08.09 at 14:49
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

use LaborDigital\Typo3BetterApi\Translation\TranslationLabelProvider;
use TYPO3\CMS\Extbase\Utility\BetterApiClassOverrideCopy__LocalizationUtility;

class ExtendedLocalizationUtility extends BetterApiClassOverrideCopy__LocalizationUtility
{
    /**
     * @inheritDoc
     */
    public static function translate(
        $key,
        $extensionName = null,
        $arguments = null,
        string $languageKey = null,
        array $alternativeLanguageKeys = null
    ) {
        return TranslationLabelProvider::provideLabelFor($key,
            function ($input) use ($extensionName, $arguments, $languageKey, $alternativeLanguageKeys) {
                return parent::translate($input, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys);
            });
    }
}
