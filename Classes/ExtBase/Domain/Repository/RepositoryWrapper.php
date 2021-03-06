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
/**
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
 * Last modified: 2020.03.20 at 18:04
 */

namespace LaborDigital\T3ba\ExtBase\Domain\Repository;

use LaborDigital\T3ba\Core\Di\NoDiInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class RepositoryWrapper extends BetterRepository implements NoDiInterface
{
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Repository
     */
    protected $repository;
    
    public function initialize(Repository $repository): void
    {
        $this->repository = $repository;
        $this->selfReference = $repository;
    }
    
    public function __get($name)
    {
        if (! property_exists($this->repository, $name)) {
            return null;
        }
        
        return $this->repository->$name;
    }
    
    public function __set($name, $value)
    {
        if (! property_exists($this->repository, $name)) {
            return;
        }
        $this->repository->$name = $value;
    }
    
    public function __isset($name)
    {
        return property_exists($this->repository, $name) && isset($this->repository->$name);
    }
    
    /**
     * @param   string  $methodName
     * @param   array   $arguments
     *
     * @return mixed|null
     */
    public function __call($methodName, $arguments)
    {
        if (! method_exists($this->repository, $methodName)) {
            return null;
        }
        
        return call_user_func_array([$this->repository, $methodName], $arguments);
    }
    
    /**
     * @inheritDoc
     */
    public function findAll()
    {
        return $this->repository->findAll();
    }
    
    /**
     * @inheritDoc
     */
    public function add($object): void
    {
        $this->repository->add($object);
    }
    
    /**
     * @inheritDoc
     */
    public function remove($object): void
    {
        $this->repository->remove($object);
    }
    
    /**
     * @inheritDoc
     */
    public function update($modifiedObject): void
    {
        $this->repository->update($modifiedObject);
    }
    
    /**
     * @inheritDoc
     */
    public function countAll(): int
    {
        return $this->repository->countAll();
    }
    
    /**
     * @inheritDoc
     */
    public function removeAll(): void
    {
        $this->repository->removeAll();
    }
    
    /**
     * @inheritDoc
     */
    public function findByUid($uid): object
    {
        return $this->repository->findByUid($uid);
    }
    
    /**
     * @inheritDoc
     */
    public function findByIdentifier($identifier): object
    {
        return $this->repository->findByIdentifier($identifier);
    }
    
    /**
     * @inheritDoc
     */
    public function setDefaultOrderings(array $defaultOrderings): void
    {
        $this->repository->setDefaultOrderings($defaultOrderings);
    }
    
    /**
     * @inheritDoc
     */
    public function setDefaultQuerySettings(QuerySettingsInterface $defaultQuerySettings): void
    {
        $this->repository->setDefaultQuerySettings($defaultQuerySettings);
    }
    
    /**
     * @inheritDoc
     */
    public function createQuery(): QueryInterface
    {
        return $this->repository->createQuery();
    }
}
