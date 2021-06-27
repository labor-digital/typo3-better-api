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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\T3ba\ExtBase\Domain\Repository;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\ExtBase\Domain\DomainException;
use LaborDigital\T3ba\Tool\Database\BetterQuery\AbstractBetterQuery;
use LaborDigital\T3ba\Tool\Database\BetterQuery\ExtBase\BetterQueryPreparationTrait;
use LaborDigital\T3ba\Tool\Database\BetterQuery\ExtBase\ExtBaseBetterQuery;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;
use TYPO3\CMS\Extbase\Persistence\Repository;

abstract class BetterRepository extends Repository
{
    use BetterQueryPreparationTrait;
    use ContainerAwareTrait;
    
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
     */
    protected $dataMapper;
    
    /**
     * When set, it will contain the cached table name so we don't need to build the mapping again
     *
     * @var string
     */
    protected $tableNameCache;
    
    /**
     * Is used to allow RepositoryWrapper to rewrite the $this variable to the stored repository
     *
     * @var BetterRepository
     */
    protected $selfReference;
    
    /**
     * @inheritDoc
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        DataMapper $dataMapper
    )
    {
        parent::__construct($objectManager);
        $this->selfReference = $this;
        $this->objectManager = $objectManager;
        $this->dataMapper = $dataMapper;
    }
    
    /**
     * Returns the name of the database table this repository works with
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableNameCache ??
               ($this->tableNameCache = $this->dataMapper->getDataMap($this->selfReference->objectType)->getTableName());
    }
    
    /**
     * Tries to find the database table name for either a given model, or the class name of a model
     *
     * @param $modelOrClass
     *
     * @return string
     * @throws DomainException
     */
    public function getTableForModel($modelOrClass): string
    {
        if (is_object($modelOrClass)) {
            if (! $modelOrClass instanceof AbstractEntity) {
                throw new DomainException('Invalid model given! A child of AbstractEntity is required!');
            }
            $modelOrClass = get_class($modelOrClass);
        }
        if (! is_string($modelOrClass)) {
            throw new DomainException('Invalid model given! Either an object or a string are allowed!');
        }
        
        return $this->dataMapper->getDataMap($modelOrClass)->getTableName();
    }
    
    /**
     * Returns a "BetterQuery" object. This object is intended to be lightweight and easy to use.
     * It does NOT implement all the features of the typo3 extBase query builder. But it's syntax is short,
     * you can build queries everywhere on the go and don't need a repository method for queries you will
     * never need more than once in your entire project.
     *
     * The returned query is a one time only object. Every time you use this method you will receive a new
     * instance of a fresh query object.
     *
     * @return ExtBaseBetterQuery
     */
    public function getQuery(): ExtBaseBetterQuery
    {
        return $this->makeInstance(ExtBaseBetterQuery::class, [
            $this,
            $this->createQuery(),
            $this->cs()->typoContext,
            $this->getService(Session::class),
        ]);
    }
    
    /**
     * Is used to create a wrapper, providing the BetterRepository api to every extbase repository.
     * Just pass the instance of your repository into this method and you can use demands and all other magic
     * as you would with a dedicated BetterRepository.
     *
     * @param   \TYPO3\CMS\Extbase\Persistence\Repository  $repository
     *
     * @return BetterRepository
     */
    public static function getWrapper(Repository $repository): BetterRepository
    {
        if ($repository instanceof self) {
            return $repository;
        }
        // @todo migrate this away from objectManager
        $wrapper = TypoContext::getInstance()->di()->cs()->objectManager->get(RepositoryWrapper::class);
        $wrapper->initialize($repository);
        
        return $wrapper;
    }
    
    /**
     * @inheritDoc
     * @noinspection PhpUnusedParameterInspection
     */
    protected function prepareBetterQuery(AbstractBetterQuery $query, array $settings, array $row): AbstractBetterQuery
    {
        return $query;
    }
}
