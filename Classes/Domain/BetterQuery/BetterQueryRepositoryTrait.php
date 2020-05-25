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
 * Last modified: 2020.03.19 at 13:03
 */

namespace LaborDigital\Typo3BetterApi\Domain\BetterQuery;

use LaborDigital\Typo3BetterApi\BetterApiException;
use LaborDigital\Typo3BetterApi\Domain\Repository\BetterRepository;
use TYPO3\CMS\Extbase\Persistence\Repository;

trait BetterQueryRepositoryTrait
{
    
    /**
     * @var \LaborDigital\Typo3BetterApi\Domain\Repository\RepositoryWrapper
     */
    protected $__repositoryWrapper;
    
    /**
     * Returns a "BetterQuery" object. This object is intended to be lightweight and easy to use.
     * It does NOT implement all the features of the typo3 extBase query builder. But it's syntax is short,
     * you can build queries everywhere on the go and don't need a repository method for queries you will
     * never need more than once in your entire project.
     *
     * The returned query is a one time only object. Every time you use this method you will receive a new
     * instance of a fresh query object.
     *
     * If your current repository has a prepareQuery() method it will be called every time
     * the query object is requested with this method
     *
     * @return \LaborDigital\Typo3BetterApi\Domain\BetterQuery\BetterQuery
     * @throws \LaborDigital\Typo3BetterApi\BetterApiException
     */
    public function getQuery(): BetterQuery
    {
        if (!$this instanceof Repository) {
            throw new BetterApiException('You can use the BetterQueryRepositoryTrait only on extbase repositories!');
        }
        if (!isset($this->__repositoryWrapper)) {
            /** @noinspection PhpParamsInspection */
            $this->__repositoryWrapper = BetterRepository::getWrapper($this);
        }
        return $this->__repositoryWrapper->getQuery();
    }
}
