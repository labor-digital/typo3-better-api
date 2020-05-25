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
 * Last modified: 2020.03.20 at 16:42
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

/**
 * Class PageContentsGridConfigFilterEvent
 *
 * Dispatched when the page service renders the list of all contents on a page.
 * This can be used to add custom grid types to the mapping
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class PageContentsGridConfigFilterEvent
{
    
    /**
     * The currently requested page id
     * @var int
     */
    protected $pid;
    
    /**
     * The list of records that were resolved for this page
     * @var array
     */
    protected $records;
    
    /**
     * The list of custom grid configurations that can be mapped
     * @var array
     */
    protected $customGrids;
    
    /**
     * PageContentsGridConfigFilterEvent constructor.
     *
     * @param int   $pid
     * @param array $records
     * @param array $customGrids
     */
    public function __construct(int $pid, array $records, array $customGrids)
    {
        $this->pid = $pid;
        $this->records = $records;
        $this->customGrids = $customGrids;
    }
    
    /**
     * Returns the currently requested page id
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }
    
    /**
     * Returns the list of records that were resolved for this page
     * @return array
     */
    public function getRecords(): array
    {
        return $this->records;
    }
    
    /**
     * Used to update the list of records that were resolved for this page
     *
     * @param array $records
     *
     * @return PageContentsGridConfigFilterEvent
     */
    public function setRecords(array $records): PageContentsGridConfigFilterEvent
    {
        $this->records = $records;
        return $this;
    }
    
    /**
     * Returns the list of custom grid configurations that can be mapped
     * @return array
     */
    public function getCustomGrids(): array
    {
        return $this->customGrids;
    }
    
    /**
     * Used to update the list of custom grid configurations that can be mapped
     *
     * @param array $customGrids
     *
     * @return PageContentsGridConfigFilterEvent
     */
    public function setCustomGrids(array $customGrids): PageContentsGridConfigFilterEvent
    {
        $this->customGrids = $customGrids;
        return $this;
    }
}
