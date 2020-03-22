<?php
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

namespace LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides;

use LaborDigital\Typo3BetterApi\Event\Events\DataMapperQueryFilterEvent;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\BetterApiClassOverrideCopy__DataMapper;

class ExtendedDataMapper extends BetterApiClassOverrideCopy__DataMapper {
	
	/**
	 * @inheritDoc
	 */
	protected function getPreparedQuery(DomainObjectInterface $parentObject, $propertyName, $fieldValue = '') {
		$query = parent::getPreparedQuery($parentObject, $propertyName, $fieldValue);
		TypoEventBus::getInstance()->dispatch(($e = new DataMapperQueryFilterEvent(
			$query, $parentObject, $propertyName, $fieldValue, $this->getType(get_class($parentObject), $propertyName)
		)));
		return $e->getQuery();
	}
	
}