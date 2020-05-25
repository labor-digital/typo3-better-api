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

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option;

use LaborDigital\Typo3BetterApi\Container\TypoContainer;

/**
 * Class AbstractChildExtConfigOption
 *
 * Child configurations are useful if you want to split up big and chunky configuration options into smaller chunks.
 *
 * @package LaborDigital\Typo3BetterApi\ExtConfig\Option
 */
abstract class AbstractChildExtConfigOption extends AbstractExtConfigOption
{
    
    /**
     * The parent config option
     * @var \LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption
     */
    protected $parent;
    
    /**
     * Returns the parent object of this child configuration option
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption
     */
    public function getParent(): AbstractExtConfigOption
    {
        return $this->parent;
    }
    
    /**
     * Used by the parent option to create a new instance of this option class
     *
     * @param \LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption $parent
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractChildExtConfigOption
     */
    public static function makeInstance(AbstractExtConfigOption $parent): AbstractChildExtConfigOption
    {
        $i = TypoContainer::getInstance()->get(static::class);
        $parent->makeCacheFileName('foo');
        $i->parent = $parent;
        $i->setContext($parent->context);
        $parent->context->EventBus->addSubscriber($i);
        return $i;
    }
}
