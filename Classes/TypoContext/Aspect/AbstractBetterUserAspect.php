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
 * Last modified: 2020.03.19 at 13:04
 */

namespace LaborDigital\Typo3BetterApi\TypoContext\Aspect;


use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use stdClass;
use TYPO3\CMS\Core\Context\UserAspect;

abstract class AbstractBetterUserAspect extends UserAspect {
	use AutomaticAspectGetTrait;
	
	/**
	 * @var TypoContext
	 */
	protected $context;
	
	/**
	 * @var \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication
	 */
	protected $resolvedUser;
	
	/**
	 * Inject the typo context instance
	 *
	 * @param \LaborDigital\Typo3BetterApi\TypoContext\TypoContext $context
	 */
	public function injectContext(TypoContext $context) {
		$this->context = $context;
	}
	
	/**
	 * @inheritDoc
	 */
	public function get(string $name) {
		return $this->handleGet($name);
	}
	
	/**
	 * Returns the root context's user aspect
	 * @return \TYPO3\CMS\Core\Context\UserAspect|mixed
	 */
	public function getRootUserAspect(): UserAspect {
		return $this->context->getRootContext()->getAspect($this->getRootAspectKey());
	}
	
	/**
	 * Returns true if there is a user object registered
	 * @return bool
	 */
	public function hasUser(): bool {
		return !empty($this->getUserObject());
	}
	
	/**
	 * @inheritDoc
	 */
	public function isLoggedIn(): bool {
		return $this->getRootUserAspect()->isLoggedIn();
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAdmin(): bool {
		return $this->getRootUserAspect()->isAdmin();
	}
	
	
	/**
	 * Returns the root aspect key
	 * @return string
	 */
	abstract protected function getRootAspectKey(): string;
	
	/**
	 * Should try to return the user object, either from the parent aspect, or by using the globals array
	 * @return \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication|mixed
	 */
	protected function getUserObject() {
		if (!empty($this->resolvedUser)) return $this->resolvedUser;
		if (!empty($this->user) && !$this->user instanceof stdClass && !is_array($this->user))
			return $this->resolvedUser = $this->user;
		$rootUser = $this->getRootUserAspect()->user;
		if (!empty($rootUser) && !$rootUser instanceof stdClass && !is_array($rootUser))
			return $this->resolvedUser = $rootUser;
		return NULL;
	}
}