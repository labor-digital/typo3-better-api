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

namespace LaborDigital\T3BA\Event\ExtBase\Persistence;

use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class DataMapperQueryFilterEvent
 *
 * Emitted when the extBase dataMapper generated a "prepared" query object
 *
 * @package LaborDigital\T3BA\Event\ExtBase\Persistence
 */
class DataMapperQueryFilterEvent
{
    
    /**
     * The database query that is currently filtered
     *
     * @var \TYPO3\CMS\Extbase\Persistence\QueryInterface
     */
    protected $query;
    
    /**
     * The domain object for which the relation is created
     *
     * @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
     */
    protected $parentObject;
    
    /**
     * The name of the property for which the relation is created
     *
     * @var string
     */
    protected $propertyName;
    
    /**
     * The value of the field that should be filtered
     *
     * @var mixed
     */
    protected $fieldValue;
    
    /**
     * The class name of the related object that should be resolved
     *
     * @var string
     */
    protected $propertyType;
    
    /**
     * DataMapperQueryFilterEvent constructor.
     *
     * @param   \TYPO3\CMS\Extbase\Persistence\QueryInterface          $query
     * @param   \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface  $parentObject
     * @param   string                                                 $propertyName
     * @param                                                          $fieldValue
     * @param   string                                                 $propertyType
     */
    public function __construct(
        QueryInterface $query,
        DomainObjectInterface $parentObject,
        string $propertyName,
        $fieldValue,
        string $propertyType
    )
    {
        $this->query = $query;
        $this->parentObject = $parentObject;
        $this->propertyName = $propertyName;
        $this->fieldValue = $fieldValue;
        $this->propertyType = $propertyType;
    }
    
    /**
     * Returns the database query that is currently filtered
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
     */
    public function getQuery(): QueryInterface
    {
        return $this->query;
    }
    
    /**
     * Updates the database query that is currently filtered
     *
     * @param   \TYPO3\CMS\Extbase\Persistence\QueryInterface  $query
     *
     * @return DataMapperQueryFilterEvent
     */
    public function setQuery(QueryInterface $query): DataMapperQueryFilterEvent
    {
        $this->query = $query;
        
        return $this;
    }
    
    /**
     * Returns the domain object for which the relation is created
     *
     * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
     */
    public function getParentObject(): DomainObjectInterface
    {
        return $this->parentObject;
    }
    
    /**
     * Returns the name of the property for which the relation is created
     *
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
    
    /**
     * Returns the value of the field that should be filtered
     *
     * @return mixed
     */
    public function getFieldValue()
    {
        return $this->fieldValue;
    }
    
    /**
     * Returns the class name of the related object that should be resolved
     *
     * @return string
     */
    public function getPropertyType(): string
    {
        return $this->propertyType;
    }
}
