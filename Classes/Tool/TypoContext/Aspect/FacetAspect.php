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
 * Last modified: 2020.08.25 at 16:32
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Tool\TypoContext\Aspect;

use LaborDigital\T3BA\Tool\TypoContext\Facet\FacetInterface;
use TYPO3\CMS\Core\Context\AspectInterface;

/**
 * Class FacetAspect
 *
 * A wrapper to store our facets inside the root context's aspect storage
 *
 * @package LaborDigital\T3BA\Tool\TypoContext\Aspect
 * @see     FacetInterface
 */
class FacetAspect implements AspectInterface
{

    /**
     * The linked facet
     *
     * @var FacetInterface
     */
    protected $facet;

    /**
     * FacetAspect constructor.
     *
     * @param   FacetInterface  $facet
     */
    public function __construct(FacetInterface $facet)
    {
        $this->facet = $facet;
    }

    /**
     * @inheritDoc
     */
    public function get(?string $name = null)
    {
        return $this->facet;
    }
}
