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
 * Last modified: 2021.06.04 at 21:13
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\ContentType\Builder\Io;


use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node;

class FieldAdapter extends AbstractField
{
    /**
     * Returns the logic node of a given field instance
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField  $field
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node
     */
    public static function getNode(AbstractField $field): Node
    {
        return $field->node;
    }
}
