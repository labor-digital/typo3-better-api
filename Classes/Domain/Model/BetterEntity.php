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

namespace LaborDigital\Typo3BetterApi\Domain\Model;

use LaborDigital\Typo3BetterApi\Container\CommonServiceDependencyTrait;
use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use LaborDigital\Typo3BetterApi\LazyLoading\LazyLoadingTrait;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

abstract class BetterEntity extends AbstractEntity
{
    use CommonServiceLocatorTrait;
    use LazyLoadingTrait;
    use CommonServiceDependencyTrait {
        CommonServiceDependencyTrait::getInstanceOf insteadof CommonServiceLocatorTrait;
        CommonServiceDependencyTrait::injectContainer insteadof CommonServiceLocatorTrait;
    }
    
    /**
     * @param int|string|null $pid
     */
    public function setPid($pid)
    {
        if (is_string($pid)) {
            $pid = $this->getService(TypoContext::class)->Pid()->get($pid, (int)$pid);
        }
        parent::setPid($pid);
    }
}
