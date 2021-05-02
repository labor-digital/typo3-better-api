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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\T3ba\ExtBase\Domain\ExtendedRelation;

use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class ExtendedRelationQueryResult extends QueryResult
{
    
    /**
     * @var ExtendedRelationService
     */
    protected $extendedRelationService;
    
    /**
     * The settings for the extended relation service
     *
     * @var array
     */
    protected $settings;
    
    /**
     * @inheritDoc
     */
    public function __construct(QueryInterface $query, array $settings)
    {
        parent::__construct($query);
        $this->settings = $settings;
    }
    
    /**
     * @param   ExtendedRelationService  $extendedRelationService
     */
    public function injectExtendedRelationService(ExtendedRelationService $extendedRelationService)
    {
        $this->extendedRelationService = $extendedRelationService;
    }
    
    /**
     * @inheritDoc
     */
    protected function initialize()
    {
        $this->extendedRelationService->runWithRelationSettings($this->settings, function () {
            parent::initialize();
        });
    }
    
    /**
     * @inheritDoc
     */
    public function getFirst()
    {
        return $this->extendedRelationService->runWithRelationSettings($this->settings, function () {
            return parent::getFirst();
        });
    }
}
