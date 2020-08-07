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
 * Last modified: 2020.03.19 at 18:54
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\BackendListLabelFilterEventAdapter;
use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\CoreHookEventInterface;

/**
 * Class BackendListLabelFilterEvent
 *
 * Called when the backend tries to render the label of a list entry of a tt_content element.
 * Mostly for use in the backend preview service
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 * @see     \LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewService
 */
class BackendListLabelFilterEvent implements CoreHookEventInterface
{

    /**
     * The name of the table that is currently rendered
     *
     * @var string
     */
    protected $tableName;

    /**
     * The database row of the record to render the the label for
     *
     * @var array
     */
    protected $row;

    /**
     * The title/label to be rendered for the record
     *
     * @var string
     */
    protected $title;

    /**
     * Additional options for the label
     *
     * @var array
     */
    protected $options;

    /**
     * @inheritDoc
     */
    public static function getAdapterClass(): string
    {
        return BackendListLabelFilterEventAdapter::class;
    }

    /**
     * BackendListLabelFilterEvent constructor.
     *
     * @param   string  $tableName
     * @param   array   $row
     * @param   string  $title
     * @param   array   $options
     */
    public function __construct(string $tableName, array $row, string $title, array $options)
    {
        $this->tableName = $tableName;
        $this->row       = $row;
        $this->title     = $title;
        $this->options   = $options;
    }

    /**
     * Returns the name of the table that is currently rendered
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Return the database row of the record to render the the label for
     *
     * @return array
     */
    public function getRow(): array
    {
        return $this->row;
    }

    /**
     * Updates the database row of the record to render the the label for
     *
     * @param   array  $row
     *
     * @return $this
     */
    public function setRow(array $row): self
    {
        $this->row = $row;

        return $this;
    }

    /**
     * Returns additional options for the label
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns the title/label to be rendered for the record
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the title/label to be rendered for the record
     *
     * @param   string  $title
     *
     * @return BackendListLabelFilterEvent
     */
    public function setTitle(string $title): BackendListLabelFilterEvent
    {
        $this->title = $title;

        return $this;
    }
}
