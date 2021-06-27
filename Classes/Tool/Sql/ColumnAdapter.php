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
 * Last modified: 2021.05.10 at 18:57
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Sql;


use Doctrine\DBAL\Schema\Column;
use LaborDigital\T3ba\Core\Di\NoDiInterface;

class ColumnAdapter extends Column implements NoDiInterface
{
    
    /**
     * Helper to inherit the configuration from the $new column into the $target column.
     * name and namespace will be preserved!
     *
     * @param   \Doctrine\DBAL\Schema\Column  $target
     * @param   \Doctrine\DBAL\Schema\Column  $new
     */
    public static function inheritConfig(Column $target, Column $new): void
    {
        foreach (get_object_vars($new) as $k => $v) {
            if ($k === '_name' || $k === '_namespace') {
                continue;
            }
            $target->$k = $new->$k;
        }
    }
}
