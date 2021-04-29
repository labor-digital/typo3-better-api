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


use LaborDigital\T3BA\Core\Di\CommonServices;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\Tool\Sql\SqlRegistry;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Io\Dumper;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Io\Factory;
use Psr\Container\ContainerInterface;

/**
 * Class TcaBuilderServices
 *
 * @package LaborDigital\T3BA\Tool\Tca\Builder
 *
 * @property ExtConfigContext $extConfigContext
 * @property SqlRegistry      $sqlRegistry
 * @property Factory          $flexFormFactory
 * @property Dumper           $flexFormDumper
 */
class TcaBuilderServices extends CommonServices
{
    /**
     * @inheritDoc
     */
    public function __construct(ContainerInterface $container, ExtConfigContext $extConfigContext)
    {
        parent::__construct($container);
        
        // Register static instances
        $this->def['extConfigContext'] = $extConfigContext;
        $this->def['sqlRegistry'] = SqlRegistry::class;
        $this->def['flexFormFactory'] = Factory::class;
        $this->def['flexFormDumper'] = Dumper::class;
    }
    
    
}
