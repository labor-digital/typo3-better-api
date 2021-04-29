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
 * Last modified: 2021.02.08 at 16:00
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Sql;


interface SqlFieldLength
{
    public const TINY_TEXT = 255;
    public const TEXT = 65535;
    public const MEDIUM_TEXT = 16777215;
    public const LONG_TEXT = null;
}
