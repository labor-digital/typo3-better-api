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
 * Last modified: 2020.06.22 at 14:40
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Http\Routing\Aspect;


use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class StoragePidAwarePersistedAliasMapper extends PersistedAliasMapper
{
    /**
     * Extends the default query builder with an additional storage pid restriction
     *
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected function createQueryBuilder(): QueryBuilder
    {
        $qb = parent::createQueryBuilder();
        if (empty($this->settings['storagePids'])) {
            return $qb;
        }

        // Add additional restrictions
        $restrictions = $qb->getRestrictions();
        $restrictions->add(
            GeneralUtility::makeInstance(
                StoragePidQueryRestriction::class,
                $this->settings['storagePids']
            )
        );

        return $qb;
    }

}
