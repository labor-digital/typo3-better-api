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
 * Last modified: 2021.01.14 at 18:43
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder;


use LaborDigital\T3BA\Core\DependencyInjection\CommonServices;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableSqlBuilder;
use LaborDigital\T3BA\Tool\TypoContext\Facet\DependencyInjectionFacet;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;

/**
 * Class TcaBuilderServices
 *
 * @package LaborDigital\T3BA\Tool\Tca\Builder
 *
 * @property ExtConfigContext                                                  $extConfigContext
 * @property DependencyInjectionFacet                                          $di
 * @property \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableSqlBuilder $sqlBuilder
 */
class TcaBuilderServices extends CommonServices
{
    /**
     * @inheritDoc
     */
    public function __construct(ExtConfigContext $extConfigContext)
    {
        parent::__construct();

        // Allow static instance lookup
        $generator       = $this->generator;
        $this->generator = static function ($i, bool $new = false) use ($generator) {
            return is_object($i) ? $i : $generator($i, $new);
        };

        // Register static instances
        $this->def['extConfigContext'] = [$extConfigContext];
        $this->def['di']               = [TypoContext::getInstance()->di()];
        $this->def['sqlBuilder']       = [TableSqlBuilder::class];
    }


}
