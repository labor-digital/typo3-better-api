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
 * Last modified: 2021.05.05 at 10:17
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\ExtBase\Hydrator;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3ba\Tool\Tca\ContentType\ContentTypeUtil;
use LaborDigital\T3ba\Tool\Tca\ContentType\Domain\AbstractDataModel;
use LaborDigital\T3ba\Tool\Tca\TcaUtil;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

class Hydrator implements PublicServiceInterface
{
    use ContainerAwareTrait;
    
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
     */
    protected $dataMapper;
    
    /**
     * @var \LaborDigital\T3ba\Tool\ExtBase\Hydrator\CacheContextAwareDataMapFactory
     */
    protected $contextAwareDataMapFactory;
    
    public function __construct(DataMapper $dataMapper, CacheContextAwareDataMapFactory $contextAwareDataMapFactory)
    {
        $this->dataMapper = $dataMapper;
        $this->contextAwareDataMapFactory = $contextAwareDataMapFactory;
    }
    
    /**
     * This method leverages the extBase data mapper to create a new entity class for a given row.
     *
     * @param   string  $modelClass  The name of the class to use as entity
     * @param   array   $row         The row to map to the model class
     *
     * @return \TYPO3\CMS\Extbase\DomainObject\AbstractEntity|AbstractDataModel
     */
    public function hydrateObject(string $modelClass, array $row): AbstractEntity
    {
        $tableName = $this->resolveTableName($modelClass);
        
        if ($tableName === 'tt_content') {
            return $this->executeContentHydration($modelClass, $row);
        }
        
        return $this->executeHydration($modelClass, $tableName, $row);
    }
    
    /**
     * Resolves a data map based on the type and subtype configured for the given row
     *
     * @param   string  $modelClass
     * @param   array   $row
     *
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap
     */
    public function getDataMap(string $modelClass, array $row): DataMap
    {
        $tableName = $this->resolveTableName($modelClass);
        
        return $this->runWithPreparedDataMapper(
            $this->getContext($tableName, $row),
            function (DataMapper $dataMapper) use ($modelClass) {
                return $dataMapper->getDataMap($modelClass);
            }
        );
    }
    
    /**
     * Returns the instance of the data mapper
     *
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
     */
    public function getDataMapper(): DataMapper
    {
        return $this->dataMapper;
    }
    
    /**
     * Builds the context string for a row of a given table.
     * The context is a string identifier for the type and subtype of the given row
     *
     * @param   string  $tableName
     * @param   array   $row
     *
     * @return string|null
     */
    public function getContext(string $tableName, array $row): ?string
    {
        $typeField = $GLOBALS['TCA'][$tableName]['ctrl']['type'] ?? null;
        $typeName = empty($typeField) ? null : TcaUtil::getRowValue($row, $typeField);
        if (empty($typeName)) {
            return null;
        }
        
        $subTypeField = empty($typeField) ? null : $GLOBALS['TCA'][$tableName]['types'][$typeName]['subtype_value_field'] ?? null;
        $subTypeName = empty($subTypeField) ? null : TcaUtil::getRowValue($row, $typeField);
        
        return trim($typeName . '*' . $subTypeName, '*');
    }
    
    /**
     * Internal helper to resolve the name of the database table for a given model class
     *
     * @param   string  $modelClass
     *
     * @return string
     * @throws \LaborDigital\T3ba\Tool\ExtBase\Hydrator\HydratorException
     */
    protected function resolveTableName(string $modelClass): string
    {
        if (! class_exists($modelClass)) {
            throw new HydratorException('The given model class $modelClass does not exist!');
        }
        
        if (! in_array(AbstractEntity::class, class_parents($modelClass), true)) {
            throw new HydratorException('The given model class $modelClass does not extend the AbstractEntity class!');
        }
        
        return NamingUtil::resolveTableName($modelClass);
    }
    
    /**
     * Executes the callback with the local data mapper prepared for the given context
     *
     * @param   string|null  $context   The result of getContext()
     * @param   callable     $callback  The callback to execute
     *
     * @return mixed
     */
    protected function runWithPreparedDataMapper(?string $context, callable $callback)
    {
        $factoryBackup = DataMapperAdapter::getFactory($this->dataMapper);
        
        try {
            if (! empty($context)) {
                $this->contextAwareDataMapFactory->setCacheContext($context);
                DataMapperAdapter::setFactory($this->dataMapper, $this->contextAwareDataMapFactory);
            }
            
            return $callback($this->dataMapper);
            
        } finally {
            DataMapperAdapter::setFactory($this->dataMapper, $factoryBackup);
        }
    }
    
    /**
     * The ContentType tool chain applies special needs to the tt_content table.
     * This special branch of the hydration will take care of the process by incorporating the TCA adjustments and
     * special hydration of additional properties
     *
     * @param   string  $modelClass
     * @param   array   $row
     *
     * @return \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
     */
    protected function executeContentHydration(string $modelClass, array $row): AbstractEntity
    {
        $cType = TcaUtil::getRowValue($row, 'CType');
        
        if (empty($cType)) {
            return $this->executeHydration($modelClass, 'tt_content', $row);
        }
        
        return ContentTypeUtil::runWithRemappedTca($cType, function () use ($modelClass, $row) {
            $mapped = $this->executeHydration($modelClass, 'tt_content', $row);
            if ($mapped instanceof AbstractDataModel) {
                $mapped->_setProperty('__raw', $row);
                $mapped->_setProperty('__flex', $this->resolveFlexFormColumns($row));
                $mapped->_memorizeCleanState('__raw');
                $mapped->_memorizeCleanState('__flex');
            }
            
            return $mapped;
        });
    }
    
    /**
     * Internal helper that executes the actual hydration of the row into the model class, by preparing the environment and configuration
     *
     * @param   string  $modelClass
     * @param   string  $tableName
     * @param   array   $row
     *
     * @return \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
     */
    protected function executeHydration(string $modelClass, string $tableName, array $row): AbstractEntity
    {
        return $this->runWithPreparedDataMapper(
            $this->getContext($tableName, $row),
            static function (DataMapper $dataMapper) use ($modelClass, $row) {
                // Handle localized uids
                $uid = $row['uid'];
                if (isset($row['_LOCALIZED_UID'])) {
                    $row['uid'] = $row['_LOCALIZED_UID'];
                } elseif (isset($row['_PAGES_OVERLAY_UID'])) {
                    $row['uid'] = $row['_PAGES_OVERLAY_UID'];
                }
                
                // Perform the mapping
                $mapped = $dataMapper->map($modelClass, [$row]);
                $mapped = reset($mapped);
                /** @var AbstractEntity $mapped */
                
                if ($uid !== $mapped->getUid()) {
                    $mapped->_setProperty('uid', $uid);
                    $mapped->_memorizeCleanState('uid');
                }
                
                return $mapped;
            }
        );
    }
    
    /**
     * Internal helper to resolve the flex form columns into their array representation
     *
     * @param   array  $row
     *
     * @return array
     */
    protected function resolveFlexFormColumns(array $row): array
    {
        $flexFormService = $this->getService(FlexFormService::class);
        $colConfig = $GLOBALS['TCA']['tt_content']['columns'] ?? [];
        $flexCols = [];
        foreach ($colConfig as $col => $conf) {
            if (empty($row[$col])
                || ($conf['config']['type'] ?? null) !== 'flex') {
                continue;
            }
            
            $flexCols[$col] = $flexFormService->convertFlexFormContentToArray($row[$col]);
        }
        
        return $flexCols;
    }
}