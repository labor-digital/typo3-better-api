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

class TranslationFileUnit
{
    /**
     * The id / key for this translation pair
     *
     * @var string
     */
    public $id;
    
    /**
     * The source language value for this id
     *
     * @var string
     */
    public $source;
    
    /**
     * The target value for this language, or null if this is the base/origin file or if $isNote is true
     *
     * @var string|null
     */
    public $target;
    
    /**
     * Contains the content of "note" nodes if $isNote is true, otherwise null
     *
     * @var string|null
     */
    public $note;
    
    /**
     * True if this unit is a note block
     *
     * @var bool
     */
    public $isNote = false;
}
