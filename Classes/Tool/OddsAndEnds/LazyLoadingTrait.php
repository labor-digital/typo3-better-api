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
/**
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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\T3ba\Tool\OddsAndEnds;

use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;

trait LazyLoadingTrait
{
    
    /**
     * Helper that always returns the real value of a given object.
     * LazyLoadingProxies and LazyObjectStorage will be converted to their real instance if required.
     *
     * @param   LazyObjectStorage|LazyLoadingProxy|mixed  $value  The object to convert to the real value
     *
     * @return object|mixed|null The converted, real value of the given value
     */
    public function getRealValue($value)
    {
        return LazyLoadingUtil::getRealValue($value);
    }
    
    /**
     * Tries to return the uid of the given entity.
     * If a lazyLoadingProxy is given, it will try to retrieve the uid of the linked element
     * even without loading the real entity from the database.
     *
     * Note that this only works with objects that have a getUid() method or are of type LazyLoadingProxy!
     *
     * @param $object
     *
     * @return int
     */
    public function getObjectUid($object): int
    {
        return LazyLoadingUtil::getObjectUid($object);
    }
}
