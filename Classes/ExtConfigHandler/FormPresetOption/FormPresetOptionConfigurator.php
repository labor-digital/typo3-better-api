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
 * Last modified: 2021.10.26 at 14:23
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\FormPresetOption;


use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigConfigurator;
use Neunerlei\Configuration\State\ConfigState;

class FormPresetOptionConfigurator extends AbstractExtConfigConfigurator
{
    /**
     * Defines the default value for the "asInt" option on date fields.
     * If this is false, all date fields will be defined as "datetime" instead of "int"
     *
     * @var bool
     */
    public $dateAsInt = true;
    
    /**
     * Defines a base dir for ALL file fields that support the "baseDir" option.
     *
     * @var string
     */
    public $fileDefaultBaseDir = '';
    
    /**
     * If set to true, setting the "baseDir" option on supporting fields, will automatically
     * enable the "file upload" and "add file by url" buttons.
     *
     * @var bool
     */
    public $fileBaseDirEnablesUpload = true;
    
    /**
     * Default value for the "overrideChildShowItem" option on file presets
     *
     * @var bool
     */
    public $fileOverrideChildShowItem = true;
    
    /**
     * A list of file extensions, that should be allowed if nothing else was defined.
     * Works similar to $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'].
     *
     * @var string
     */
    public $fileDefaultAllowList = [];
    
    /**
     * A list of sys_file_reference fields that should BY DEFAULT be disabled
     * in inline FAL field definitions. Can be overridden using "disableFalFields"
     *
     * @var array
     */
    public $fileDefaultDisabledFalFields = [];
    
    
    /**
     * @inheritDoc
     */
    public function finish(ConfigState $state): void
    {
        parent::finish($state);
    }
    
}