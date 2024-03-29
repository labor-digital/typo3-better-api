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
 * Last modified: 2021.11.08 at 18:34
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Event\BackendPreview;

/**
 * Called when the backend tries to render the label of a table row,
 * that registered a list label renderer through "setListLabelRenderer"
 */
class TableListLabelRenderingEvent extends AbstractListLabelRenderingEvent
{
    
    /**
     * The name of the registered handler to be executed for the table
     *
     * @var string
     */
    protected $handler;
    
    /**
     * @inheritDoc
     */
    public function __construct(string $tableName, array $row, string $title, array $options, string $handler)
    {
        parent::__construct($tableName, $row, $title, $options);
        $this->handler = $handler;
    }
    
    /**
     * Returns the name of the registered handler to be executed for the table
     *
     * @return string
     */
    public function getHandler(): string
    {
        return $this->handler;
    }
}