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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits;


use Neunerlei\Arrays\Arrays;

trait FactoryTypeLoaderTrait
{
    /**
     * Finds the list of all types in the given TCA
     *
     * @param   array  $tca
     *
     * @return array
     */
    protected function findTypes(array $tca): array
    {
        $types = [];
        foreach (Arrays::getPath($tca, 'types', []) as $k => $v) {
            if (! is_array($v)) {
                continue;
            }
            
            $types[(string)$k] = $v;
        }
        
        return $types;
    }
    
    /**
     * Returns the typeName of the default type in the TCA. The default type will be
     * represented by the table object itself
     *
     * @param   array       $tca
     * @param   array|null  $types
     *
     * @return string
     */
    protected function findDefaultTypeName(array $tca, ?array $types = null): string
    {
        $types = is_array($types) ? $types : $this->findTypes($tca);
        if (empty($types)) {
            return '1';
        }
        
        return (string)key($types);
    }
}
