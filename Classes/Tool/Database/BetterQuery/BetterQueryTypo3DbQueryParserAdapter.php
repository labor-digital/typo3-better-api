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
 * Last modified: 2020.08.28 at 11:17
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Tool\Database\BetterQuery;

use LaborDigital\T3BA\Core\DependencyInjection\StaticContainerAwareTrait;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;

class BetterQueryTypo3DbQueryParserAdapter extends Typo3DbQueryParser
{
    use StaticContainerAwareTrait;

    /**
     * Sadly not all features of ext base are implemented using the doctrine restrictions.
     * So I use the settings object internally and force the constraints using the db query parser object
     * on the query builder
     *
     * @param   string                                                         $tableName
     * @param   \TYPO3\CMS\Core\Database\Query\QueryBuilder                    $queryBuilder
     * @param   \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface  $settings
     */
    public static function addConstraintsOfSettings(
        string $tableName,
        QueryBuilder $queryBuilder,
        QuerySettingsInterface $settings
    ): void {
        $self               = static::getSingletonOf(Typo3DbQueryParser::class);
        $self->queryBuilder = $queryBuilder;
        $dummyQuery         = new Query('');
        $dummyQuery->setQuerySettings($settings);
        $self->tableAliasMap             = [];
        $self->tableAliasMap[$tableName] = $tableName;
        $self->addTypo3Constraints($dummyQuery);
    }
}
