<?php
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

namespace LaborDigital\Typo3BetterApi\Domain\Repository;


use LaborDigital\Typo3BetterApi\Container\CommonServiceDependencyTrait;
use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\Domain\BetterQuery\BetterQuery;
use LaborDigital\Typo3BetterApi\Domain\BetterQuery\BetterQueryPreparationTrait;
use LaborDigital\Typo3BetterApi\Domain\DomainException;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Repository;

abstract class BetterRepository extends Repository {
	use CommonServiceLocatorTrait;
	use BetterQueryPreparationTrait;
	use CommonServiceDependencyTrait {
		CommonServiceDependencyTrait::getInstanceOf insteadof CommonServiceLocatorTrait;
		CommonServiceDependencyTrait::injectContainer insteadof CommonServiceLocatorTrait;
	}
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
	 */
	protected $container;
	
	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
	 */
	protected $dataMapper;
	
	/**
	 * When set, it will contain the cached table name so we don't need to build the mapping again
	 * @var string
	 */
	protected $tableNameCache;
	
	/**
	 * Is used to allow RepositoryWrapper to rewrite the $this variable to the stored repository
	 * @var \LaborDigital\Typo3BetterApi\Domain\Repository\BetterRepository
	 */
	protected $selfReference;
	
	/**
	 * @inheritDoc
	 */
	public function __construct(ObjectManagerInterface $objectManager,
								TypoContainerInterface $container, DataMapper $dataMapper) {
		parent::__construct($objectManager);
		$this->selfReference = $this;
		$this->objectManager = $objectManager;
		$this->container = $container;
		$this->dataMapper = $dataMapper;
	}
	
	/**
	 * Returns the name of the database table this repository works with
	 * @return string
	 */
	public function getTableName(): string {
		if (isset($this->tableNameCache)) return $this->tableNameCache;
		return $this->tableNameCache = $this->dataMapper->getDataMap($this->selfReference->objectType)->getTableName();
	}
	
	/**
	 * Tries to find the database table name for either a given model, or the class name of a model
	 *
	 * @param $modelOrClass
	 *
	 * @return string
	 * @throws \LaborDigital\Typo3BetterApi\Domain\DomainException
	 */
	public function getTableForModel($modelOrClass): string {
		if (is_object($modelOrClass)) {
			if (!$modelOrClass instanceof AbstractEntity)
				throw new DomainException("Invalid model given! A child of AbstractEntity is required!");
			$modelOrClass = get_class($modelOrClass);
		}
		if (!is_string($modelOrClass))
			throw new DomainException("Invalid model given! Either an object or a string are allowed!");
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
	 * @return \LaborDigital\Typo3BetterApi\Domain\BetterQuery\BetterQuery
	 */
	public function getQuery(): BetterQuery {
		return $this->container->get(BetterQuery::class, ["args" => [$this, $this->createQuery()]]);
	}
	
	/**
	 * Is used to create a wrapper, providing the BetterRepository api to every extbase repository.
	 * Just pass the instance of your repository into this method and you can use demands and all other magic
	 * as you would with a dedicated BetterRepository.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Repository $repository
	 *
	 * @return \LaborDigital\Typo3BetterApi\Domain\Repository\BetterRepository
	 */
	public static function getWrapper(Repository $repository): BetterRepository {
		if ($repository instanceof BetterRepository) return $repository;
		$wrapper = TypoContainer::getInstance()->get(RepositoryWrapper::class);
		$wrapper->__initialize($repository);
		return $wrapper;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareBetterQuery(BetterQuery $query, array $settings, array $row): BetterQuery {
		return $query;
	}
	
}