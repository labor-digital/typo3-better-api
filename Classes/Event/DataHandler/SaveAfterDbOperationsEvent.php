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
 * Last modified: 2020.10.18 at 18:21
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event\DataHandler;


/**
 * Class SaveAfterUpdateEvent
 *
 * Executed once for every table record that was processed in the data handler
 *
 * @package LaborDigital\T3BA\Event\DataHandler
 */
class SaveAfterDbOperationsEvent extends AbstractLateSaveEvent
{
    /**
     * The id of the entry that is saved.
     *
     * @var int
     */
    protected $id;

    /**
     * Returns the id of the entry that is saved
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}