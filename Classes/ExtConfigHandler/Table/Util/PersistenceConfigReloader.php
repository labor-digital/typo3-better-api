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
 * Last modified: 2021.11.05 at 19:10
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Table\Util;


use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Extbase\Persistence\ClassesConfigurationFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;

class PersistenceConfigReloader implements PublicServiceInterface
{
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ClassesConfigurationFactory
     */
    protected $configurationFactory;
    
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory
     */
    protected $dataMapFactory;
    
    /**
     * @var \TYPO3\CMS\Core\Cache\CacheManager
     */
    protected $cacheManager;
    
    public function __construct(
        ClassesConfigurationFactory $configurationFactory,
        DataMapFactory $dataMapFactory,
        CacheManager $cacheManager
    )
    {
        $this->configurationFactory = $configurationFactory;
        $this->dataMapFactory = $dataMapFactory;
        $this->cacheManager = $cacheManager;
    }
    
    /**
     * Flushes the "extbase" cache, reloads the persistence class config and injects it forcefully
     * into the dataMapFactory, so that our late-injected mappings are available even if required
     * before the TCA was built completely
     */
    public function reload(): void
    {
        $this->cacheManager->getCache('extbase')->flush();
        
        $ref = new \ReflectionObject($this->dataMapFactory);
        
        $classesConfiguration = $this->configurationFactory->createClassesConfiguration();
        $propRef = $ref->getProperty('classesConfiguration');
        $propRef->setAccessible(true);
        $propRef->setValue($this->dataMapFactory, $classesConfiguration);
        $propRef->setAccessible(false);
        
        $propRef = $ref->getProperty('dataMaps');
        $propRef->setAccessible(true);
        $propRef->setValue($this->dataMapFactory, []);
        $propRef->setAccessible(false);
    }
}