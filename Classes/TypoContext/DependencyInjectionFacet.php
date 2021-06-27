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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\TypoContext;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Tool\TypoContext\FacetInterface;

/**
 * Repository of all dependency injection capabilities of TYPO3
 */
class DependencyInjectionFacet implements FacetInterface
{
    use ContainerAwareTrait {
        getContainer as public;
        getService as public;
        getServiceOrInstance as public;
        makeInstance as public;
        getCommonServices as public;
        cs as public;
    }
    
    /**
     * @inheritDoc
     */
    public static function getIdentifier(): string
    {
        return 'di';
    }
}
