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
 * Last modified: 2020.03.20 at 18:22
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\ExtBaseAfterPersistObjectEventAdapter;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

class ExtBaseAfterPersistObjectEvent implements CoreHookEventInterface
{
    
    /**
     * The domain object that was persisted
     *
     * @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
     */
    protected $object;
    
    /**
     * @inheritDoc
     */
    public static function getAdapterClass(): string
    {
        return ExtBaseAfterPersistObjectEventAdapter::class;
    }
    
    /**
     * ExtBaseAfterPersistObjectEvent constructor.
     *
     * @param   \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface  $object
     */
    public function __construct(DomainObjectInterface $object)
    {
        $this->object = $object;
    }
    
    /**
     * Returns the domain object that was persisted
     *
     * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
     */
    public function getObject(): DomainObjectInterface
    {
        return $this->object;
    }
    
    /**
     * Updates the domain object that was persisted
     *
     * @param   \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface  $object
     *
     * @return ExtBaseAfterPersistObjectEvent
     */
    public function setObject(DomainObjectInterface $object): ExtBaseAfterPersistObjectEvent
    {
        $this->object = $object;
        
        return $this;
    }
}
