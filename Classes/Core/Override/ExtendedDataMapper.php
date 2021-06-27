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
 * Last modified: 2020.03.19 at 02:04
 */

namespace LaborDigital\T3ba\Core\Override;


use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Event\ExtBase\Persistence\DataMapperQueryFilterEvent;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\T3BaCopyDataMapper;

class ExtendedDataMapper extends T3BaCopyDataMapper
{
    /**
     * @inheritDoc
     */
    protected function getPreparedQuery(DomainObjectInterface $parentObject, $propertyName, $fieldValue = '')
    {
        $query = parent::getPreparedQuery($parentObject, $propertyName, $fieldValue);
        TypoEventBus::getInstance()->dispatch(($e = new DataMapperQueryFilterEvent(
            $query,
            $parentObject,
            $propertyName,
            $fieldValue,
            $this->getType(get_class($parentObject), $propertyName)
        )));
        
        return $e->getQuery();
    }
}
