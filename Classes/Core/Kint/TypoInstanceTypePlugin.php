<?php
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
 * Last modified: 2020.08.23 at 23:23
 */

namespace LaborDigital\T3BA\Core\Kint;

use Kint\Object\BasicObject;
use Kint\Object\InstanceObject;
use Kint\Parser\Parser;
use Kint\Parser\Plugin;
use LaborDigital\T3BA\Core\Util\LazyLoadingUtil;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class TypoInstanceTypePlugin extends Plugin
{
    
    /**
     * @inheritDoc
     */
    public function getTypes()
    {
        return ['object'];
    }
    
    public function getTriggers()
    {
        return Parser::TRIGGER_COMPLETE;
    }
    
    /** @noinspection ReferencingObjectsInspection */
    public function parse(&$variable, BasicObject &$o, $trigger)
    {
        // Show the iterator first
        if (! empty($o->getRepresentation('iterator'))) {
            $r = $o->getRepresentation('iterator');
            $o->removeRepresentation('iterator');
            $o->addRepresentation($r, 0);
        }
        
        // Add the uid of entities to the output
        if ($variable instanceof AbstractEntity && $o instanceof InstanceObject) {
            $o->classname = $o->classname .= ' - UID: ' . $variable->getUid();
        }
        
        // Remove the iterator representation from lazy objects
        if ($variable instanceof LazyObjectStorage || $variable instanceof LazyLoadingProxy) {
            $o->removeRepresentation('iterator');
        }
        
        // Add the real class name to lazy loading proxies
        if ($variable instanceof LazyLoadingProxy) {
            $realVal = LazyLoadingUtil::getRealValue($variable);
            if ($realVal !== null) {
                $o->classname .= ' - ' . get_class($realVal) .
                                 ($realVal instanceof AbstractEntity ? ' - UID: ' . $realVal->getUid() : '');
            }
        }
        
        // Update size for countable objects
        if ($variable instanceof ObjectStorage || $variable instanceof QueryResultInterface) {
            $o->size = $variable->count();
        }
    }
}
