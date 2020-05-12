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
 * Last modified: 2020.03.22 at 14:14
 */

namespace PHPSTORM_META {
	
	use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
	use LaborDigital\Typo3BetterApi\Container\ContainerAwareTrait;
	use LaborDigital\Typo3BetterApi\Container\LazyServiceDependencyTrait;
	use LaborDigital\Typo3BetterApi\Container\TypoContainer;
	use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
	use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
	use Psr\Container\ContainerInterface;
	use TYPO3\CMS\Core\Utility\GeneralUtility;
	use TYPO3\CMS\Extbase\Object\ObjectManager;
	use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
	
	// TYPO3 Core
	override(GeneralUtility::makeInstance(0), type(0));
	override(ObjectManager::get(0), type(0));
	override(ObjectManagerInterface::get(0), type(0));
	override(ContainerInterface::get(0), type(0));
	
	// Better API
	override(TypoContainer::get(0), type(0));
	override(TypoContainerInterface::get(0), type(0));
	override(ExtConfigContext::getInstanceOf(0), type(0));
	override(ContainerAwareTrait::getInstance(0), type(0));
	override(LazyServiceDependencyTrait::getInstance(0), type(0));
	override(LazyServiceDependencyTrait::getService(0), type(0));
	
	// Deprecated
	override(CommonServiceLocatorTrait::getInstanceOf(0), type(0));
}