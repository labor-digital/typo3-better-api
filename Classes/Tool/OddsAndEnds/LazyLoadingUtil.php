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
 * Last modified: 2020.08.27 at 21:58
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\OddsAndEnds;


use LaborDigital\T3BA\Core\Exception\T3BAException;
use ReflectionObject;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class LazyLoadingUtil
{
    /**
     * Helper that always returns the real value of a given object.
     * LazyLoadingProxies and LazyObjectStorage will be converted to their real instance if required.
     *
     * @param   LazyObjectStorage|LazyLoadingProxy|mixed  $value  The object to convert to the real value
     *
     * @return object|mixed|null The converted, real value of the given value
     */
    public static function getRealValue($value)
    {
        if (is_object($value)) {
            if ($value instanceof LazyLoadingProxy) {
                return $value->_loadRealInstance();
            }
            if ($value instanceof LazyObjectStorage) {
                $res = new ObjectStorage();
                foreach ($value as $v) {
                    $res->attach($v);
                }

                return $res;
            }
        }

        return $value;
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
     * @throws \LaborDigital\T3BA\Core\Exception\T3BAException
     */
    public static function getObjectUid($object): int
    {
        if (! is_object($object)) {
            throw new T3BAException('getObjectUid() accepts only object instances!');
        }
        if ($object instanceof AbstractEntity || method_exists($object, 'getUid')) {
            return $object->getUid();
        }
        if ($object instanceof LazyLoadingProxy) {
            $propRef = (new ReflectionObject($object))->getProperty('fieldValue');
            $propRef->setAccessible('true');
            $value = $propRef->getValue($object);
            if (is_numeric($value)) {
                return (int)$value;
            }
            throw new T3BAException('The given object\'s proxy did not return a numeric value for its uid!');
        }
        throw new T3BAException('getObjectUid() could not find an option to return the entities UID');
    }
}
