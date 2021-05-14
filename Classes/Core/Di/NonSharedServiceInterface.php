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
 * Last modified: 2021.05.10 at 17:16
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Core\Di;

/**
 * Interface NonSharedServiceInterface
 *
 * Marks the class that has this interface as not shared, meaning
 * every time you request it from the container, a new instance is created.
 *
 * @package LaborDigital\T3ba\Core\Di
 * @see     https://symfony.com/doc/current/service_container/shared.html
 */
interface NonSharedServiceInterface
{
    
}