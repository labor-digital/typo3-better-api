<?php
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
 * Last modified: 2020.10.18 at 17:31
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\TypoContext\Facet;


use LaborDigital\T3BA\Core\DependencyInjection\ContainerAwareTrait;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class DependencyInjectionFacet implements FacetInterface
{
    use ContainerAwareTrait {
        getContainer as public;
        getInstanceOf as public;
        getSingletonOf as public;
        getWithoutDi as public;
        getCommonServices as public;
        cs as public;
    }

    /**
     * Returns the ext base object manager instance
     *
     * @todo I think this should be removed -> It's part of the common services api
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    public function getObjectManager(): ObjectManager
    {
        return $this->getWithoutDi(ObjectManager::class);
    }
}
