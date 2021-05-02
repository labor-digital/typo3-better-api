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


namespace LaborDigital\T3ba\Core\Kint;

use Kint\Object\BasicObject;
use Kint\Object\InstanceObject;
use Kint\Parser\IteratorPlugin;
use Kint\Parser\Parser;
use Kint\Parser\Plugin;
use LaborDigital\T3ba\Tool\OddsAndEnds\LazyLoadingUtil;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;

class LazyLoadingPlugin extends Plugin
{
    /**
     * @var \Kint\Parser\Parser
     */
    protected $parser;
    
    public function getTypes(): array
    {
        return ['object'];
    }
    
    public function getTriggers()
    {
        return Parser::TRIGGER_BEGIN;
    }
    
    public function parse(&$variable, BasicObject &$o, $trigger)
    {
        if ($variable instanceof LazyObjectStorage) {
            /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $realVar */
            $realVar = LazyLoadingUtil::getRealValue($variable);
            $realVarArray = Arrays::makeFromObject($realVar);
            
            /** @var InstanceObject $object */
            $object = InstanceObject::blank($o->name);
            $object->transplant($o);
            $object->classname = get_class($realVar);
            $object->depth = $o->depth + 1;
            
            $object2 = InstanceObject::blank($o->name);
            $object2 = $this->parser->parse($realVarArray, $object2);
            $representations = $object2->getRepresentations();
            $object->addRepresentation(reset($representations));
            $o = $object;
            $o->type = 'object';
            $o->size = $realVar->count();
            IteratorPlugin::$blacklist[] = $object->classname;
        } elseif ($variable instanceof LazyLoadingProxy) {
            $realVar = LazyLoadingUtil::getRealValue($variable);
            $object = BasicObject::blank($o->name);
            $object->transplant($o);
            $object->depth = $o->depth;
            $o = $this->parser->parse($realVar, $object);
        }
    }
}
