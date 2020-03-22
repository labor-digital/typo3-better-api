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
 * Last modified: 2020.03.20 at 16:30
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Domain\BetterQuery;


use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;

class BetterQueryTypo3DbQueryParserAdapter extends Typo3DbQueryParser {
	
	/**
	 * The singleton instance to avoid overhead
	 * @var Typo3DbQueryParser
	 */
	protected static $concreteQueryParser;
	
	/**
	 * Sadly not all features of ext base are implemented using the doctrine restrictions.
	 * So I use the settings object internally and force the constraints using the db query parser object
	 * on the query builder
	 *
	 * @param string                                                        $tableName
	 * @param \TYPO3\CMS\Core\Database\Query\QueryBuilder                   $queryBuilder
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $settings
	 */
	public static function addConstraintsOfSettings(string $tableName, QueryBuilder $queryBuilder, QuerySettingsInterface $settings): void {
		$self = static::getConcreteQueryParser();
		$self->queryBuilder = $queryBuilder;
		$dummyQuery = new Query("");
		$dummyQuery->setQuerySettings($settings);
		$self->tableAliasMap[$tableName] = $tableName;
		$self->addTypo3Constraints($dummyQuery);
	}
	
	/**
	 * Internal helper to access the instance of the query parser object
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser
	 */
	public static function getConcreteQueryParser(): Typo3DbQueryParser {
		if (!empty(static::$concreteQueryParser)) return static::$concreteQueryParser;
		return static::$concreteQueryParser = TypoContainer::getInstance()->get(Typo3DbQueryParser::class);
	}
}