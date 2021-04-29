<?php
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
 * Last modified: 2020.08.25 at 11:01
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event\TypoScript;


class FileImportFilterEvent
{
    /**
     * Full absolute path+filename to the typoScript file to be included
     *
     * @var string
     */
    protected $filename;
    
    /**
     * Counter for detecting endless loops
     *
     * @var int
     */
    protected $cycleCounter;
    
    /**
     * When set, filenames of included files will be prepended to the array $includedFiles
     *
     * @var bool
     */
    protected $returnFiles;
    
    /**
     * Array to which the filenames of included files will be prepended (referenced)
     *
     * @var array
     */
    protected $includedFiles;
    
    /**
     * If set this value will be returned instead of the default file handling
     *
     * @var string|null
     */
    protected $result;
    
    /**
     * ImportExternalTypoScriptFileEvent constructor.
     *
     * @param   string  $filename
     * @param   int     $cycleCounter
     * @param   bool    $returnFiles
     * @param   array   $includedFiles
     */
    public function __construct(
        string $filename,
        int $cycleCounter,
        bool $returnFiles,
        array $includedFiles
    )
    {
        $this->filename = $filename;
        $this->cycleCounter = $cycleCounter;
        $this->returnFiles = $returnFiles;
        $this->includedFiles = $includedFiles;
    }
    
    /**
     * Returns the full absolute path+filename to the typoScript file to be included
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
    
    /**
     * Sets the full absolute path+filename to the typoScript file to be included
     *
     * @param   string  $filename
     *
     * @return FileImportFilterEvent
     */
    public function setFilename(string $filename): FileImportFilterEvent
    {
        $this->filename = $filename;
        
        return $this;
    }
    
    /**
     * Returns the counter for detecting endless loops
     *
     * @return int
     */
    public function getCycleCounter(): int
    {
        return $this->cycleCounter;
    }
    
    /**
     * Updates the counter for detecting endless loops
     *
     * @param   int  $cycleCounter
     *
     * @return FileImportFilterEvent
     */
    public function setCycleCounter(int $cycleCounter): FileImportFilterEvent
    {
        $this->cycleCounter = $cycleCounter;
        
        return $this;
    }
    
    /**
     * Returns true if filenames of included files will be prepended to the array $includedFiles
     *
     * @return bool
     */
    public function doesReturnFiles(): bool
    {
        return $this->returnFiles;
    }
    
    /**
     * Updates if filenames of included files will be prepended to the array $includedFiles
     *
     * @param   bool  $returnFiles
     *
     * @return FileImportFilterEvent
     */
    public function setReturnFiles(bool $returnFiles): FileImportFilterEvent
    {
        $this->returnFiles = $returnFiles;
        
        return $this;
    }
    
    /**
     * Returns the array to which the filenames of included files will be prepended (referenced)
     *
     * @return array
     */
    public function getIncludedFiles(): array
    {
        return $this->includedFiles;
    }
    
    /**
     * Updates the array to which the filenames of included files will be prepended (referenced)
     *
     * @param   array  $includedFiles
     *
     * @return FileImportFilterEvent
     */
    public function setIncludedFiles(array $includedFiles): FileImportFilterEvent
    {
        $this->includedFiles = $includedFiles;
        
        return $this;
    }
    
    /**
     * Returns the value which will be returned instead of the default file handling or null if none was registered
     *
     * @return string|null
     */
    public function getResult(): ?string
    {
        return $this->result;
    }
    
    /**
     * If set this value will be returned instead of the default file handling
     *
     * @param   string|null  $result
     *
     * @return FileImportFilterEvent
     */
    public function setResult(?string $result): FileImportFilterEvent
    {
        $this->result = $result;
        
        return $this;
    }
    
}
