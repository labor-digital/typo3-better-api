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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\Table;


use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractTab;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Traits\LayoutMetaTrait;

class TcaTab extends AbstractTab
{
    use LayoutMetaTrait;
    
    /**
     * Returns the instance of a certain field inside your current layout
     * Note: If the field not exists, a new one will be created at the end of the form
     *
     * @param   string     $id                   The id / column name of this field in the database
     * @param   bool|null  $ignoreFieldIdIssues  If set to true, the field id will not be validated
     *                                           against TYPO3s field naming schema
     *
     * @return TcaField
     * @throws \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\InvalidFieldIdException
     */
    public function getField(string $id, ?bool $ignoreFieldIdIssues = null): TcaField
    {
        return $this->getForm()->getField($id, $ignoreFieldIdIssues);
    }
}
