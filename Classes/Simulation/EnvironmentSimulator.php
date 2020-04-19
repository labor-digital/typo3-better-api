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
 * Last modified: 2020.03.20 at 13:59
 */

namespace LaborDigital\Typo3BetterApi\Simulation;


use InvalidArgumentException;
use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use Neunerlei\Options\Options;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class EnvironmentSimulator
 *
 * This class is highly experimental, there may be bugs on your way!
 *
 * @package LaborDigital\Typo3BetterApi\Simulation
 */
class EnvironmentSimulator implements SingletonInterface {
	use CommonServiceLocatorTrait;
	
	/**
	 * This is true when we are currently running inside a transformation
	 * @var bool
	 */
	protected $isInSimulation = FALSE;
	
	/**
	 * True if child simulations should be ignored
	 * @var bool
	 */
	protected $childSimulationsIgnored = FALSE;
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\Simulation\AdminUserAuthentication
	 */
	protected $adminBackendUser;
	
	/**
	 * Can be used to run a function in a different environment.
	 * You can use this method to render frontend content's in the backend or in the CLI,
	 * but you can also use it in the frontend to change the context PID / the language to something different.
	 *
	 * @param array    $options The options to define the new environment
	 *                          - pid int: Can be used to change the page id of the executed process.
	 *                          If this is left empty the current page id is used
	 *                          - language int|string|SiteLanguage: The language to set the environment to.
	 *                          Either as sys_language_uid value, as iso code or as language object
	 *                          - fallbackLanguage int|string|SiteLanguage: The language which should be used when the
	 *                          $language was not found for this site. If true is given, the default language will be
	 *                          used
	 *                          - bootTsfe bool (TRUE): By default the simulator will start a dummy version of
	 *                          the frontend controller and populate the $GLOBALS["TSFE"] variable with it.
	 *                          If you set this to false, the TSFE is not booted; it may already exist tho!
	 *                          - ignoreChildSimulations bool (FALSE): If this is set to true nested calls
	 *                          of this simulator will be skipped and the handler will be executed in the
	 *                          environment of the parent simulator
	 *                          - ignoreInSimulations bool (FALSE): If this is set to TRUE the simulation
	 *                          will not be done when the script is currently running inside another simulation.
	 *                          - ignoreIfFrontendExists bool (FALSE): If this is set to true there will be no
	 *                          simulation if we detect an already initialized TSFE. Useful to let frontend
	 *                          code run in the backend without adding overhead to the frontend
	 *                          - includeHiddenPages bool (FALSE): If this is set to true the closure will
	 *                          have access to all hidden pages.
	 *                          - includeHiddenContent bool (FALSE): If this is set to true the closure will
	 *                          have access to all hidden content elements on when retrieving tt_content data
	 *                          - includeDeletedRecords bool (FALSE): If this is set to true the requests
	 *                          made in the closure will include deleted records
	 * @param callable $handler The callback to execute inside the modified context
	 *
	 * @return mixed|null
	 */
	public function runWithEnvironment(array $options, callable $handler) {
		// Prepare the options
		$options = Options::make($options, [
			"pid"                    => [
				"type"    => ["int", "null"],
				"default" => NULL,
			],
			"language"               => [
				"type"    => ["int", "string", "null", SiteLanguage::class],
				"default" => NULL,
			],
			"fallbackLanguage"       => [
				"type"    => ["int", "string", "null", SiteLanguage::class, "true"],
				"default" => NULL,
			],
			"ignoreChildSimulations" => [
				"type"    => "bool",
				"default" => $this->childSimulationsIgnored,
			],
			"ignoreInSimulations"    => [
				"type"    => "bool",
				"default" => FALSE,
			],
			"ignoreIfFrontendExists" => [
				"type"    => "bool",
				"default" => FALSE,
			],
			"bootTsfe"               => [
				"type"    => "bool",
				"default" => TRUE,
			],
			"includeHiddenPages"     => [
				"type"    => "bool",
				"default" => FALSE,
			],
			"includeHiddenContent"   => [
				"type"    => "bool",
				"default" => FALSE,
			],
			"includeDeletedRecords"  => [
				"type"    => "bool",
				"default" => FALSE,
			],
		]);
		
		// Check if the simulation should be ignored
		$ignoreSimulation = FALSE;
		
		// Ignore if we should ignore child simulations
		if ($this->isInSimulation && $options["ignoreInSimulations"]) $ignoreSimulation = TRUE;
		else if ($this->isInSimulation && $this->childSimulationsIgnored) $ignoreSimulation = TRUE;
		
		// Ignore, because frontend exists
		if (!$ignoreSimulation && $options["ignoreIfFrontendExists"] && $this->Tsfe->hasTsfe()) $ignoreSimulation = TRUE;
		
		// Check if we want a simulation to happen
		$doSimulation = FALSE;
		if (!$ignoreSimulation) {
			// Simulate if we require a frontend but don't have one
			if ($options["bootTsfe"] && !$this->Tsfe->hasTsfe()) $doSimulation = TRUE;
			
			// Simulate if we have a language mismatch
			if (!$doSimulation && ($options["language"] !== NULL)) {
				$languageObject = $this->resolveLanguageObject($options["language"], $options["fallbackLanguage"]);
				if ($languageObject->getLanguageId() !== $this->TypoContext->getLanguageAspect()->getCurrentFrontendLanguage()->getLanguageId())
					$doSimulation = TRUE;
			}
			
			// Simulate if we have to change the pid
			if (!$doSimulation && $options["pid"] !== NULL) {
				if ($this->TypoContext->getPidAspect()->getCurrentPid() !== $options["pid"])
					$doSimulation = TRUE;
			}
			
			// Simulate if we have a visibility mismatch
			if (!$doSimulation && $options["includeHiddenPages"] !== $this->TypoContext->getVisibilityAspect()->includeHiddenPages())
				$doSimulation = TRUE;
			if (!$doSimulation && $options["includeHiddenContent"] !== $this->TypoContext->getVisibilityAspect()->includeHiddenContent())
				$doSimulation = TRUE;
			if (!$doSimulation && $options["includeDeletedRecords"] !== $this->TypoContext->getVisibilityAspect()->includeDeletedRecords())
				$doSimulation = TRUE;
		}
		
		// Backup the old ignore child simulation state
		$parentIgnoresChildSimulations = $this->childSimulationsIgnored;
		$this->childSimulationsIgnored = $options["ignoreChildSimulations"];
		$parentIsInSimulation = $this->isInSimulation;
		$this->isInSimulation = $this->isInSimulation || $doSimulation;
		
		// Execute the simulation if required
		$result = NULL;
		try {
			if ($doSimulation) {
				// Backup current values
				$backup = new class {
					public $restoreLanguage = FALSE;
					public $tsfe;
					public $langService;
					public $langAspect;
					public $language;
					public $typoRequest;
					public $visibility;
					public $pids;
				};
				
				// Run the handler
				try {
					
					// Context
					// =============================
					$backup->pids = $this->TypoContext->getPidAspect()->getAllPids();
					
					// Visibility
					// =============================
					if ($options["includeHiddenPages"] || $options["includeHiddenContent"] || $options["includeDeletedRecords"]) {
						$visibilityAspect = $this->TypoContext->getVisibilityAspect();
						$backup->visibility = [
							"includeHiddenPages"    => $visibilityAspect->includeHiddenPages(),
							"includeHiddenContent"  => $visibilityAspect->includeHiddenContent(),
							"includeDeletedRecords" => $visibilityAspect->includeDeletedRecords(),
						];
						$visibilityAspect->setIncludeHiddenPages($options["includeHiddenPages"]);
						$visibilityAspect->setIncludeHiddenContent($options["includeHiddenContent"]);
						$visibilityAspect->setIncludeDeletedRecords($options["includeDeletedRecords"]);
					}
					
					// LANGUAGE
					// =============================
					if ($options["language"] !== NULL) {
						// Backup all elements we touch
						$backup->restoreLanguage = TRUE;
						$backup->language = $this->TypoContext->getLanguageAspect()->getCurrentFrontendLanguage();
						if (isset($GLOBALS["LANG"])) $backup->langService = $GLOBALS["LANG"];
						$backup->langAspect = $this->TypoContext->getRootContext()->getAspect("language");
						if (isset($GLOBALS["TYPO3_REQUEST"])) $backup->typoRequest = $GLOBALS['TYPO3_REQUEST'];
						
						// Create updated elements
						$language = $this->resolveLanguageObject($options["language"], $options["fallbackLanguage"]);
						if (isset($GLOBALS['TYPO3_REQUEST']) && $GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface)
							$GLOBALS['TYPO3_REQUEST'] = $GLOBALS['TYPO3_REQUEST']->withAttribute("language", $language);
						unset($GLOBALS["LANG"]);
						$languageAspect = LanguageAspectFactory::createFromSiteLanguage($language);
						$this->TypoContext->getRootContext()->setAspect("language", $languageAspect);
						$this->Translation->getTypoLanguageService();
					}
					
					// Tsfe
					// =============================
					$backup->tsfe = $GLOBALS["TSFE"];
					if ($this->Tsfe->hasTsfe() || $options["bootTsfe"]) {
						$pid = is_null($options["pid"]) ? $this->TypoContext->getPidAspect()->getCurrentPid() : $options["pid"];
						$this->makeDummyTsfe($pid);
						// Make sure the language aspect stays the same way as we set it...
						if (isset($languageAspect)) $this->TypoContext->getRootContext()->setAspect("language", $languageAspect);
					}
					
					// Run the handler
					$result = call_user_func($handler);
					
				} finally {
					
					// Revert the backup
					// Tsfe
					// =============================
					$GLOBALS["TSFE"] = $backup->tsfe;
					
					// LANGUAGE
					// =============================
					if ($backup->restoreLanguage) {
						$GLOBALS["LANG"] = $backup->langService;
						$GLOBALS['TYPO3_REQUEST'] = $backup->typoRequest;
						$this->TypoContext->getRootContext()->setAspect("language", $backup->langAspect);
					}
					
					// Visibility
					// =============================
					if (!empty($backup->visibility)) {
						$visibilityAspect = $this->TypoContext->getVisibilityAspect();
						$visibilityAspect->setIncludeHiddenPages($backup->visibility["includeHiddenPages"]);
						$visibilityAspect->setIncludeHiddenContent($backup->visibility["includeHiddenContent"]);
						$visibilityAspect->setIncludeDeletedRecords($backup->visibility["includeDeletedRecords"]);
					}
					
					// Context
					// =============================
					$this->TypoContext->getPidAspect()->__setPids($backup->pids);
					
					// Clean up
					$backup = NULL;
					unset($backup);
				}
			} else {
				// Run handler normally if we don't need to do something special...
				$result = call_user_func($handler);
			}
		} finally {
			// Restore parent state
			$this->childSimulationsIgnored = $parentIgnoresChildSimulations;
			$this->isInSimulation = $parentIsInSimulation;
		}
		
		// Done
		return $result;
	}
	
	/**
	 * ATTENTION: This method is extremely powerful and you should really consider twice if you want to use it
	 * for whatever you want to achieve.
	 *
	 * What it does: This method allows execution of the given $callback as an administrator user!
	 * This will allow you to circumvent all permissions, access rights and user groups that are configured in the
	 * backend. There are no safeguards here! It's like writing data directly into the database; If you don't know what
	 * you do inside the scope of this method's callback you can break a lot of stuff...
	 *
	 * This works in the Frontend, in the Backend in the CLI and wherever else.
	 *
	 * It will create a new backend user called _betterApi_adminUser_ for you which is used as user object
	 * inside the closure. Every action performed, and logged will be executed as this user; except you are
	 * already logged in as an administrator, in that case we just use your account!
	 *
	 * So again. Use it, but please use it with care!
	 *
	 * @param callable $handler A callback to be executed in the context of an administrator
	 *
	 * @return mixed
	 */
	public function runAsAdmin(callable $handler) {
		// Store the current backend user
		$backupBackendUser = $GLOBALS["BE_USER"];
		
		// Check if we are already an admin -> nothing more to worry about...
		if ($backupBackendUser instanceof BackendUserAuthentication && $backupBackendUser->isAdmin())
			return call_user_func($handler);
		
		// Backup the current context
		$userAspectBackup = $this->TypoContext->getBeUserAspect();
		
		// Instantiate the translation service if it is not there yet
		$this->Translation->getTypoLanguageService();
		
		// Create the instance of the admin authentication
		if (empty($this->adminBackendUser)) {
			$user = $this->getInstanceOf(AdminUserAuthentication::class);
			$user->start();
			$this->adminBackendUser = $user;
		}
		
		// Check if we can create a more speaking log
		unset($this->adminBackendUser->user["ses_backuserid"]);
		if ($backupBackendUser instanceof BackendUserAuthentication && is_array($backupBackendUser->user) && !empty($backupBackendUser->user["uid"]))
			$this->adminBackendUser->user["ses_backuserid"] = $backupBackendUser->user["uid"];
		
		// Update the global user object
		$GLOBALS["BE_USER"] = $this->adminBackendUser;
		
		// Update the backend user aspect
		$this->TypoContext->getRootContext()->setAspect("backend.user", $this->getInstanceOf(UserAspect::class, [$this->adminBackendUser]));
		
		// Execute the callback
		$result = call_user_func($handler);
		
		// Restore the global user object
		$GLOBALS["BE_USER"] = $backupBackendUser;
		
		// Restore the backend user context
		$this->TypoContext->getRootContext()->setAspect("backend.user", $userAspectBackup);
		
		// Done
		return $result;
	}
	
	/**
	 * Internal helper to resolve the language by a multitude of different formats
	 *
	 * @param int|string|SiteLanguage      $language         The language to set the frontend to.
	 *                                                       Either as sys_language_uid value or as language object
	 *
	 * @param int|string|SiteLanguage|true $fallbackLanguage The language which should be used when the $language was
	 *                                                       not found for this site. If true is given, the default
	 *                                                       language will be used
	 *
	 * @return mixed|\TYPO3\CMS\Core\Site\Entity\SiteLanguage
	 */
	protected function resolveLanguageObject($language, $fallbackLanguage = NULL) {
		if (!is_object($language)) {
			$languages = $this->TypoContext->getSiteAspect()->getSite()->getLanguages();
			foreach ($languages as $lang) {
				if (is_numeric($language) && $lang->getLanguageId() === (int)$language || strtolower($lang->getTwoLetterIsoCode()) == $language) {
					$language = $lang;
					break;
				}
			}
		}
		if (!$language instanceof SiteLanguage) {
			if (!is_null($fallbackLanguage)) {
				if ($fallbackLanguage === TRUE) $fallbackLanguage = $this->TypoContext->getSiteAspect()->getSite()->getDefaultLanguage();
				return $this->resolveLanguageObject($fallbackLanguage);
			}
			throw new InvalidArgumentException("Could not determine the site language for the given language value!");
		}
		return $language;
	}
	
	/**
	 * Internal helper that is used to create a new tsfe instance
	 *
	 * It is not fully initialized and also not available on $GLOBALS['TSFE'],
	 * but should do the trick for most of your needs
	 *
	 * @param int $pid The pid to create the controller instance with
	 *
	 * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	protected function makeDummyTsfe(int $pid): TypoScriptFrontendController {
		$GLOBALS['TSFE'] = $controller = $this->getInstanceOf(TypoScriptFrontendController::class, [NULL, $pid, 0,]);
		$controller->sys_page = $this->getInstanceOf(PageRepository::class);
		$controller->rootLine = $this->getInstanceOf(RootlineUtility::class, [$pid])->get();
		$controller->page = $this->Page->getPageInfo($pid);
		$controller->getConfigArray();
		$controller->settingLanguage();
		$controller->settingLocale();
		$controller->cObj = $this->getInstanceOf(ContentObjectRenderer::class, [$controller]);
		$controller->fe_user = $this->getInstanceOf(FrontendUserAuthentication::class);
		return $controller;
	}
	
	
}