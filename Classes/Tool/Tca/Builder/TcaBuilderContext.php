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


namespace LaborDigital\T3ba\Tool\Tca\Builder;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;

class TcaBuilderContext implements NoDiInterface
{
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderServices
     */
    protected $commonServices;
    
    /**
     * TcaBuilderContext constructor.
     *
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext  $extConfigContext
     */
    public function __construct(ExtConfigContext $extConfigContext)
    {
        $this->commonServices = $extConfigContext->getLoaderContext()->getInstance(TcaBuilderServices::class);
    }
    
    /**
     * Returns the ext config context used to run this builder
     *
     * @return \LaborDigital\T3ba\ExtConfig\ExtConfigContext
     */
    public function getExtConfigContext(): ExtConfigContext
    {
        return $this->commonServices->extConfigContext;
    }
    
    /**
     * Returns a extended version of the normal CommonServices object which contains
     * additional services specific to the tca builder context
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderServices
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
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderServices
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
    
    /**
     * Helper which is used to generate a list of valid table names.
     * It will always return an array of table names. If a comma separated string is given, it will be broken up into
     * an array, if a ...table shorthand is given it will be resolved to the current extension's table name.
     * Non-unique items will be dropped
     *
     * @param   string|array  $tableInput
     *
     * @return array
     * @throws \LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException
     */
    public function getRealTableNameList($tableInput): array
    {
        if (! is_array($tableInput)) {
            if (is_string($tableInput)) {
                $tableInput = Arrays::makeFromStringList($tableInput);
            } else {
                throw new TcaBuilderException('The given value for $table is invalid! Please use either an array of table names, or a single table as string!');
            }
        }
        
        return array_unique(
            array_map([$this, 'getRealTableName'], $tableInput)
        );
    }
}
