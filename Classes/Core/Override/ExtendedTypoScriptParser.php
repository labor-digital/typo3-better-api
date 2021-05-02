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


namespace LaborDigital\T3ba\Core\Override;


use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Event\TypoScript\FileImportFilterEvent;
use TYPO3\CMS\Core\TypoScript\Parser\T3BaCopyTypoScriptParser;

class ExtendedTypoScriptParser extends T3BaCopyTypoScriptParser
{
    /**
     * @inheritDoc
     */
    protected static function importExternalTypoScriptFile(
        $filename,
        $cycleCounter,
        $returnFiles,
        array &$includedFiles
    )
    {
        TypoEventBus::getInstance()->dispatch(
            $e = new FileImportFilterEvent(
                (string)$filename,
                (int)$cycleCounter,
                (bool)$returnFiles,
                $includedFiles)
        );
        $filename = $e->getFilename();
        $cycleCounter = $e->getCycleCounter();
        $returnFiles = $e->doesReturnFiles();
        $includedFiles = $e->getIncludedFiles();
        
        return $e->getResult() ?? parent::importExternalTypoScriptFile($filename, $cycleCounter, $returnFiles, $includedFiles);
    }
    
    
}
