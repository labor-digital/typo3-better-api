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

namespace LaborDigital\Typo3BetterApi\Translation\FileSync;

/**
 * Class TranslationFile
 *
 * @package    LaborDigital\Typo3BetterApi\Translation\FileSync
 * @deprecated will be removed in v10
 */
class TranslationFile
{
    
    /**
     * The full filename of this translation file
     *
     * @var string
     */
    public $filename;
    
    /**
     * The source language of this translation file
     *
     * @var string
     */
    public $sourceLang = 'en';
    
    /**
     * The target language (on lang files) or null if this is the origin file
     *
     * @var string|null
     */
    public $targetLang;
    
    /**
     * The product name to set for this translation file
     *
     * @var string
     */
    public $productName;
    
    /**
     * The list of translation pairs inside of this translation file
     *
     * @var \LaborDigital\Typo3BetterApi\Translation\FileSync\TranslationFileUnit[]
     */
    public $messages = [];
    
    /**
     * Additional xml attributes for the xliff tag
     *
     * @var array
     */
    public $params = [];
}
