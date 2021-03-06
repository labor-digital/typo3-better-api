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


namespace LaborDigital\T3ba\Tool\ExtBase\Hydrator;


use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;

class CacheContextAwareDataMapFactory extends DataMapFactory
{
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory
     */
    protected $concreteFactory;
    
    /**
     * @var string
     */
    protected $cacheContext;
    
    /**
     * CacheContextAwareDataMapFactory constructor.
     *
     * @param   \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory  $concreteFactory
     *
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(DataMapFactory $concreteFactory)
    {
        $this->concreteFactory = $concreteFactory;
    }
    
    /**
     * Allows the outside world to inject the cache context
     *
     * @param   string  $context
     */
    public function setCacheContext(string $context): void
    {
        $this->cacheContext = $context;
    }
    
    /**
     * @inheritDoc
     */
    public function buildDataMap(string $className): DataMap
    {
        $className = ltrim($className, '\\');
        $storageKey = $className . '_' . $this->cacheContext;
        $that = $this->concreteFactory;
        if (isset($that->dataMaps[$storageKey])) {
            return $that->dataMaps[$storageKey];
        }
        
        $cacheIdentifierClassName = str_replace('\\', '', $className);
        $cacheIdentifier = 'DataMap_' . $cacheIdentifierClassName .
                           '_' . sha1(
                               (string)(new Typo3Version()) . Environment::getProjectPath() . $this->cacheContext
                           );
        
        $dataMap = $that->dataMapCache->get($cacheIdentifier);
        
        if ($dataMap === false) {
            $dataMap = $that->buildDataMapInternal($className);
            $that->dataMapCache->set($cacheIdentifier, $dataMap);
        }
        
        $that->dataMaps[$storageKey] = $dataMap;
        
        return $dataMap;
    }
}