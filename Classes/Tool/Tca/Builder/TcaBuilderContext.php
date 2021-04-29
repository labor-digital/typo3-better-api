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


namespace LaborDigital\T3BA\Tool\Tca\Builder;


use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\Inflection\Inflector;

class TcaBuilderContext
{
    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderServices
     */
    protected $commonServices;
    
    /**
     * TcaBuilderContext constructor.
     *
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext  $extConfigContext
     */
    public function __construct(ExtConfigContext $extConfigContext)
    {
        $this->commonServices = $extConfigContext->getLoaderContext()->getInstance(TcaBuilderServices::class);
    }
    
    /**
     * Returns the ext config context used to run this builder
     *
     * @return \LaborDigital\T3BA\ExtConfig\ExtConfigContext
     */
    public function getExtConfigContext(): ExtConfigContext
    {
        return $this->commonServices->extConfigContext;
    }
    
    /**
     * Returns a extended version of the normal CommonServices object which contains
     * additional services specific to the tca builder context
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderServices
     * @see cs() for a short hand
     */
    public function getCommonServices(): TcaBuilderServices
    {
        return $this->commonServices;
    }
    
    /**
     * Shorthand alias of: getCommonServices()
     * Returns a extended version of the normal CommonServices object which contains
     * additional services specific to the tca builder context
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderServices
     * @see getCommonServices()
     */
    public function cs(): TcaBuilderServices
    {
        return $this->commonServices;
    }
    
    /**
     * Helper which is used to unfold the "..." prefixed table names to a ext base, default table name
     *
     * @param   string|mixed  $tableName
     *
     * @return string
     * @see NamingUtil::resolveTableName() is used for all table names that don't start with
     */
    public function getRealTableName($tableName): string
    {
        if (! is_string($tableName) || strpos(trim($tableName), '...') !== 0) {
            return NamingUtil::resolveTableName($tableName);
        }
        
        return implode('_', array_filter([
            'tx',
            NamingUtil::flattenExtKey($this->getExtConfigContext()->getExtKey()),
            'domain',
            'model',
            strtolower(Inflector::toCamelBack(substr(trim($tableName), 3))),
        ]));
    }
}
