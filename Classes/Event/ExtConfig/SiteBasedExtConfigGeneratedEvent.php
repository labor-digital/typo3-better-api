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
 * Last modified: 2021.11.15 at 12:42
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Event\ExtConfig;

/**
 * Emitted when the MAIN SITE-BASED ext config for ALL SITES was generated.
 * Allows you to modify/filter the config state before it is persisted into the cache
 */
class SiteBasedExtConfigGeneratedEvent extends AbstractExtConfigGeneratedEvent
{
    
}