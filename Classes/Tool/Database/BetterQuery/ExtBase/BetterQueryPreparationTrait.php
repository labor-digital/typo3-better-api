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
 * Last modified: 2020.08.28 at 10:52
 */

namespace LaborDigital\T3ba\Tool\Database\BetterQuery\ExtBase;

use LaborDigital\T3ba\Tool\Database\BetterQuery\AbstractBetterQuery;
use LaborDigital\T3ba\Tool\Database\BetterQuery\BetterQueryException;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;
use Neunerlei\TinyTimy\DateTimy;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

trait BetterQueryPreparationTrait
{
    
    /**
     * Should return a new better query instance
     *
     * @return AbstractBetterQuery
     */
    abstract public function getQuery(): AbstractBetterQuery;
    
    /**
     * Receives the query object after the initial preparation was done and should apply additional constraints to it.
     *
     * @param   AbstractBetterQuery  $query
     * @param   array                $settings
     * @param   array                $row
     *
     * @return ExtBaseBetterQuery
     */
    abstract protected function prepareBetterQuery(
        AbstractBetterQuery $query,
        array $settings,
        array $row
    ): AbstractBetterQuery;
    
    /**
     * This method is similar to getQuery() on the BetterRepository class,
     * but it does not simply return an empty query object, no it can also take ext base plugin settings and a database
     * row to read additional information from to preconfigure your query with.
     *
     * @param   array       $settings  The ext base $this->settings value of a controller class
     * @param   array|null  $row       The row of a tt_content record containing the ext base plugin configuration.
     *
     * @return AbstractBetterQuery
     */
    public function getPreparedQuery(array $settings, ?array $row = null): AbstractBetterQuery
    {
        $query = $this->getQuery();
        $row = $row ?? [];
        
        // Fill default values
        if (! empty($settings['storagePid'])) {
            $query = $query->withPids(Arrays::makeFromStringList($settings['storagePid']));
        } elseif (! empty($row['pages'])) {
            if (is_string($row['pages'])) {
                $query = $query->withPids(Arrays::makeFromStringList($row['pages']));
            } elseif (is_array($row['pages'])) {
                $pids = [];
                foreach ($row['pages'] as $page) {
                    if (is_numeric($page)) {
                        $pids[] = $page;
                    } elseif (is_array($page) && isset($page['uid'])) {
                        $pids[] = $page['uid'];
                    }
                }
                $query = $query->withPids($pids);
            }
        }
        
        return $this->prepareBetterQuery($query, $settings, $row);
    }
    
    
    /**
     * Configures the given better query object to a date range constraint.
     * It is optional if you work on a single field or with startDate and endDate fields.
     *
     * @param   AbstractBetterQuery  $query                 The query object to configure
     * @param   array                $queryDateRangeConfig  Expects an array containing four parameters
     *                                                      "startDateField": The database field name that holds the
     *                                                      start dates
     *                                                      "endDateField": The database field name that holds the end
     *                                                      dates (is equal to "startDateField" if no the min and the
     *                                                      max date should be determined only by a single field)
     *                                                      "min": The minimum date value in the start date field
     *                                                      "max": The maximum date value in the end date field
     *
     * @return AbstractBetterQuery
     */
    protected function setQueryDateRangeConstraint(
        AbstractBetterQuery $query,
        array $queryDateRangeConfig
    ): AbstractBetterQuery
    {
        // Add the constraint to the query object
        return $query->withWhere([
            static function (QueryInterface $query) use ($queryDateRangeConfig) {
                return $query->logicalAnd([
                    $query->greaterThanOrEqual($queryDateRangeConfig['endDateField'], $queryDateRangeConfig['min']),
                    $query->lessThanOrEqual($queryDateRangeConfig['startDateField'], $queryDateRangeConfig['max']),
                ]);
            },
        ], 'dateTimeRange');
    }
    
    /**
     * Receives a query object and one/two fields that define a date range.
     * This method will query the database and calculate then min and max dates from the database.
     *
     * You can either get the range over two columns (one for the start- and one for the end date)
     * Or you can get the date range in a single column (just keep the third argument null)
     *
     * @param   AbstractBetterQuery  $query              The preconfigured query object to request the values with
     * @param   string               $startDateProperty  The ext base property name that holds the start dates of an
     *                                                   entity. Or the property name of the column that just holds the
     *                                                   dates (just start; no end dates)
     * @param   string|null          $endDateProperty    Optionally the ext base property name of the column that
     *                                                   defines the end dates of an entity.
     *
     * @return array The result is an array containing four values:
     *               "startDateField": The database field name that holds the start dates
     *               "endDateField": The database field name that holds the end dates (is equal to "startDateField" if
     *               no $endDateProperty was given)
     *               "min": The oldest entry in the start date field
     *               "max": The newest entry in the end date field
     *
     * @throws BetterQueryException
     * @noinspection PhpMethodParametersCountMismatchInspection
     */
    protected function getQueryDateRange(
        AbstractBetterQuery $query,
        string $startDateProperty,
        ?string $endDateProperty = null
    ): array
    {
        // Get the date constraints
        // Start date
        $startDateField = $startDateProperty;
        $minDate = $query->withLimit(1)->withOrder($startDateProperty, 'asc')->getFirst(true);
        if (! empty($minDate) && ! isset($minDate[$startDateField])) {
            $startDateField = Inflector::toDatabase($startDateField);
        }
        $minDate = new DateTimy(empty($minDate) || ! isset($minDate[$startDateField]) ?
            0 : $minDate[$startDateField]);
        $minDate->setTime(0, 0);
        
        // End date
        $endDateField = $endDateProperty;
        if ($endDateField === null) {
            $endDateField = $startDateProperty;
        }
        $maxDate = $query->withLimit(1)->withOrder($endDateField, 'desc')->getFirst(true);
        if (! empty($maxDate) && ! isset($maxDate[$endDateField])) {
            $endDateField = Inflector::toDatabase($endDateField);
        }
        $maxDate = new DateTimy(empty($maxDate) || ! isset($maxDate[$endDateField]) ?
            0 : $maxDate[$endDateField]);
        $maxDate->setTime(23, 59, 59);
        
        // Limit min max values
        if ($minDate > $maxDate) {
            throw new BetterQueryException('The calculated oldest date is newer than the latest date. That can\'t be true!');
        }
        
        // Done
        return [
            'startDateField' => $startDateField,
            'endDateField' => $endDateField,
            'min' => $minDate,
            'max' => $maxDate,
        ];
    }
}
