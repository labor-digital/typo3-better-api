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

namespace LaborDigital\Typo3BetterApi\Session;


use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use TYPO3\CMS\Core\SingletonInterface;

class SessionService implements SingletonInterface {
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
	 */
	protected $container;
	
	/**
	 * Session constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface $container
	 */
	public function __construct(TypoContainerInterface $container) {
		$this->container = $container;
	}
	
	/**
	 * Returns the instance of the frontend session
	 * @return \LaborDigital\Typo3BetterApi\Session\SessionInterface
	 */
	public function getFrontendSession(): SessionInterface {
		return $this->container->get(FrontendSessionProvider::class);
	}
	
	/**
	 * Returns the instance of the backend session
	 * @return \LaborDigital\Typo3BetterApi\Session\SessionInterface
	 */
	public function getBackendSession(): SessionInterface {
		return $this->container->get(BackendSessionProvider::class);
	}
}