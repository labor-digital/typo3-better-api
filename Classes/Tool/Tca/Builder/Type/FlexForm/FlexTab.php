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


namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm;


use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractTab;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\Traits\DisplayConditionTrait;

class FlexTab extends AbstractTab
{
    use DisplayConditionTrait;
    
    /**
     * Returns the instance of a certain field inside your current layout
     *
     * Note: If the field not exists, a new one will be created at the end of the form
     *
     * Note: This method supports the usage of paths. FlexForm fields inside of containers may have
     * the same id's. Which makes the lookup of such fields ambiguous. There is not much you can do about that, tho...
     * To select such fields you can either select the section and then the field inside it. Or you use paths
     * on a method like this. A path looks like: "section->field" the "->" works as a separator between the parts of
     * the path. As you see there is no Tab definition, like 0 or 1. Because we will look for "section" in all existing
     * tabs.
     *
     * @param   string  $id  The id / column name of this field in the database
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\FlexField
     */
    public function getField(string $id): FlexField
    {
        return $this->getForm()->getField($id);
    }
}
