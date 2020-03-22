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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\Domain\ExtendedRelation;


use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class ExtendedRelationQueryResult extends QueryResult {
	
	/**
	 * The settings for the extended relation service
	 * @var array
	 */
	protected $settings;
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\Domain\ExtendedRelation\ExtendedRelationService
	 */
	protected $extendedRelationService;
	
	/**
	 * @param \LaborDigital\Typo3BetterApi\Domain\ExtendedRelation\ExtendedRelationService $extendedRelationService
	 */
	public function injectExtendedRelationService(ExtendedRelationService $extendedRelationService) {
		$this->extendedRelationService = $extendedRelationService;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initialize() {
		$this->extendedRelationService->runWithRelationSettings($this->settings, function () {
			parent::initialize();
		});
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFirst() {
		return $this->extendedRelationService->runWithRelationSettings($this->settings, function () {
			return parent::getFirst();
		});
	}
	
	/**
	 * Factory method to create a new instance of myself
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $result
	 * @param array                                               $settings
	 *
	 * @return \LaborDigital\Typo3BetterApi\Domain\ExtendedRelation\ExtendedRelationQueryResult
	 */
	public static function makeInstance(QueryResultInterface $result, array $settings): ExtendedRelationQueryResult {
		$self = TypoContainer::getInstance()->get(static::class, ["args" => [$result->getQuery()]]);
		$self->settings = $settings;
		return $self;
	}
}