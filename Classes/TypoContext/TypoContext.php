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

namespace LaborDigital\Typo3BetterApi\TypoContext;

use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\TypoContext\Aspect\BetterLanguageAspect;
use LaborDigital\Typo3BetterApi\TypoContext\Aspect\BetterVisibilityAspect;
use LaborDigital\Typo3BetterApi\TypoContext\Aspect\BeUserAspect;
use LaborDigital\Typo3BetterApi\TypoContext\Aspect\EnvironmentAspect;
use LaborDigital\Typo3BetterApi\TypoContext\Aspect\FeUserAspect;
use LaborDigital\Typo3BetterApi\TypoContext\Aspect\PathAspect;
use LaborDigital\Typo3BetterApi\TypoContext\Aspect\PidAspect;
use LaborDigital\Typo3BetterApi\TypoContext\Aspect\RequestAspect;
use LaborDigital\Typo3BetterApi\TypoContext\Aspect\SiteAspect;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\SingletonInterface;

class TypoContext implements SingletonInterface {
	
	/**
	 * @var \TYPO3\CMS\Core\Context\Context
	 */
	protected $context;
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
	 */
	protected $container;
	
	/**
	 * TypoContext constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface $container
	 */
	public function __construct(TypoContainerInterface $container) {
		$this->container = $container;
	}
	
	/**
	 * Returns the TYPO3 root context
	 * @return \TYPO3\CMS\Core\Context\Context
	 */
	public function getRootContext(): Context {
		if (isset($this->context)) return $this->context;
		return $this->context = $this->container->get(Context::class);
	}
	
	/**
	 * Returns information about the visibility of records
	 * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\BetterVisibilityAspect
	 */
	public function getVisibilityAspect(): BetterVisibilityAspect {
		return $this->getOrMakeAspect("betterVisibility", BetterVisibilityAspect::class);
	}
	
	/**
	 * Returns information about the environment
	 * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\EnvironmentAspect
	 */
	public function getEnvAspect(): EnvironmentAspect {
		return $this->getOrMakeAspect("environment", EnvironmentAspect::class);
	}
	
	/**
	 * Contains a repository of path information and path resolving functions
	 * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\PathAspect
	 */
	public function getPathAspect(): PathAspect {
		return $this->getOrMakeAspect("paths", PathAspect::class);
	}
	
	/**
	 * Contains information about the current typo3 site.
	 * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\SiteAspect
	 */
	public function getSiteAspect(): SiteAspect {
		return $this->getOrMakeAspect("site", SiteAspect::class);
	}
	
	/**
	 * Returns the workspace aspect which holds information about the currently accessed workspace.
	 * @return \TYPO3\CMS\Core\Context\WorkspaceAspect|mixed
	 */
	public function getWorkspaceAspect(): WorkspaceAspect {
		return $this->context->getAspect("workspace");
	}
	
	/**
	 * Returns the language aspect of this request.
	 * Note: This is not the core language aspect, but a better language aspect,
	 * which holds additional methods to retrieve language information.
	 * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\BetterLanguageAspect
	 */
	public function getLanguageAspect(): BetterLanguageAspect {
		return $this->getOrMakeAspect("betterLanguage", BetterLanguageAspect::class);
	}
	
	/**
	 * Returns the aspect which holds information about the http request.
	 * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\RequestAspect
	 */
	public function getRequestAspect(): RequestAspect {
		return $this->getOrMakeAspect("request", RequestAspect::class);
	}
	
	/**
	 * Returns the aspect which contains information about registered pids and the local page id
	 * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\PidAspect
	 */
	public function getPidAspect(): PidAspect {
		return $this->getOrMakeAspect("pid", PidAspect::class);
	}
	
	/**
	 * Returns the frontend user context aspect
	 * @return FeUserAspect
	 */
	public function getFeUserAspect(): FeUserAspect {
		return $this->getOrMakeAspect("frontend.betterUser", FeUserAspect::class);
	}
	
	/**
	 * Returns the backend user context aspect
	 * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\BeUserAspect
	 */
	public function getBeUserAspect(): BeUserAspect {
		return $this->getOrMakeAspect("backend.betterUser", BeUserAspect::class);
	}
	
	/**
	 * Internal helper to unlink the context instance in the init step
	 */
	public function __unlinkContext(): void {
		$this->context = NULL;
	}
	
	/**
	 * Internal helper to request an aspect from the context, or if it is a custom aspect
	 * create a new instance which is then provided to the context's storage.
	 *
	 * @param string      $aspectKey   The key of the aspect to store / load from the context
	 * @param string|null $aspectClass An optional class to be instantiated.
	 *
	 * @return \TYPO3\CMS\Core\Context\AspectInterface|mixed
	 */
	protected function getOrMakeAspect(string $aspectKey, ?string $aspectClass = NULL): AspectInterface {
		$context = $this->getRootContext();
		if ($context->hasAspect($aspectKey)) return $context->getAspect($aspectKey);
		if (empty($aspectClass)) return $context->getAspect($aspectKey);
		$aspect = $this->container->get($aspectClass);
		$context->setAspect($aspectKey, $aspect);
		return $aspect;
	}
}