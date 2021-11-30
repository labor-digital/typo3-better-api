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
 * Last modified: 2021.11.29 at 21:38
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tests\Functional\Tool\Database\BetterQuery;


use LaborDigital\T3ba\Tool\Database\DbService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class StandaloneBetterQueryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad
        = [
            'typo3conf/ext/t3ba',
        ];
    
    public function testFoo(): void
    {
        $this->importDataSet('EXT:t3ba/Tests/Fixture/be_users.xml');
        $query = GeneralUtility::makeInstance(DbService::class)->getQuery('be_users');
    }
}