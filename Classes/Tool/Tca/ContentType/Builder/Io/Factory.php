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
 * Last modified: 2021.04.22 at 00:28
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\ContentType\Builder\Io;


use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableFactory;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TypeFactory;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TableDefaults;
use LaborDigital\T3BA\Tool\Tca\ContentType\Builder\ContentType;

class Factory
{
    
    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableFactory
     */
    protected $tableFactory;
    
    /**
     * @var \LaborDigital\T3BA\ExtConfig\ExtConfigContext
     */
    protected $extConfigContext;
    
    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable|null
     */
    protected $table;
    
    /**
     * The default TCA to use when a new type instance is being generated
     *
     * @var string[]
     */
    protected $defaultTypeTca = TableDefaults::CONTENT_TYPE_TCA;
    
    public function __construct(TableFactory $tableFactory, ExtConfigContext $extConfigContext)
    {
        $this->tableFactory = $tableFactory;
        $this->extConfigContext = $extConfigContext;
    }
    
    /**
     * Returns the currently configured default type tca. It is the configuration of a tt_content "type" entry.
     * It will be used when a form is generated for a type that currently does not exist.
     *
     * @return string[]
     */
    public function getDefaultTypeTca(): array
    {
        return $this->defaultTypeTca;
    }
    
    /**
     * Allows the outside world to override the type tca entry to use for new forms
     *
     * @param   string[]  $defaultTypeTca
     *
     * @return Factory
     */
    public function setDefaultTypeTca(array $defaultTypeTca): Factory
    {
        $this->defaultTypeTca = $defaultTypeTca;
        
        return $this;
    }
    
    public function getType(string $cType): ContentType
    {
        $tcaBackup = $GLOBALS['TCA']['tt_content'];
        $typeClassBackup = TypeFactory::$typeClass;
        TypeFactory::$typeClass = ContentType::class;
        
        try {
            // Simplify the type definition
            $GLOBALS['TCA']['tt_content']['types'] = [
                $cType => array_merge($this->getDefaultTypeTca(), $tcaBackup['types'][$cType] ?? []),
            ];
            
            $table = $this->tableFactory->create('tt_content', $this->extConfigContext);
            $this->tableFactory->initialize($table);
            
            /** @var ContentType $type */
            $type = $table->getType($cType);
            
            return $type;
        } finally {
            $GLOBALS['TCA']['tt_content'] = $tcaBackup;
            TypeFactory::$typeClass = $typeClassBackup;
        }
    }
}
