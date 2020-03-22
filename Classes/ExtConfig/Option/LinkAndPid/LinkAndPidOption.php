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
 * Last modified: 2020.03.18 at 19:45
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid;

use LaborDigital\Typo3BetterApi\Event\Events\ExtLocalConfLoadedEvent;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\Links\LinkSetGenerator;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\Pids\PidGenerator;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\Pids\PidTypoScriptGenerator;
use LaborDigital\Typo3BetterApi\Link\LinkSetRepository;
use LaborDigital\Typo3BetterApi\Pid\TypoScriptHook;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class LinkAndPidOption
 *
 * Can be used to configure PID's and link sets
 *
 * @package LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid
 */
class LinkAndPidOption extends AbstractExtConfigOption implements SingletonInterface {
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\Link\LinkSetRepository
	 */
	protected $linkSetRepository;
	
	/**
	 * LinkConfigOption constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\Link\LinkSetRepository $linkSetRepository
	 */
	public function __construct(LinkSetRepository $linkSetRepository) {
		$this->linkSetRepository = $linkSetRepository;
	}
	
	/**
	 * @inheritDoc
	 */
	public function subscribeToEvents(EventSubscriptionInterface $subscription) {
		$subscription->subscribe(ExtLocalConfLoadedEvent::class, "__applyExtLocalConf");
	}
	
	/**
	 * Can be used to add a link set registration class.
	 *
	 * @param string $linkSetDefinitionClass The name of a class that implements the LinkSetConfigurationInterface
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\LinkAndPidOption
	 * @see \LaborDigital\Typo3BetterApi\Link\LinkService::getLink()
	 * @see \LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\Links\LinkSetConfigurationInterface
	 */
	public function registerLinkSetDefinition(string $linkSetDefinitionClass): LinkAndPidOption {
		return $this->addRegistrationToCachedStack("linkSets", "main", $linkSetDefinitionClass);
	}
	
	/**
	 * Can be used to add a link set override class.
	 *
	 * @param string $linkSetOverrideClass The name of a class that implements the LinkSetConfigurationInterface
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\LinkAndPidOption
	 * @see \LaborDigital\Typo3BetterApi\Link\LinkService::getLink()
	 * @see \LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\Links\LinkSetConfigurationInterface
	 */
	public function registerLinkSetOverride(string $linkSetOverrideClass): LinkAndPidOption {
		return $this->addOverrideToCachedStack("linkSets", "main", $linkSetOverrideClass);
	}
	
	/**
	 * Can be used to add a pid registration class.
	 *
	 * @param string $pidDefinitionClass The name of a class that implements the PidConfigurationInterface
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\LinkAndPidOption
	 * @see \LaborDigital\Typo3BetterApi\TypoContext\Aspect\PidAspect
	 * @see \LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\Pids\PidConfigurationInterface
	 */
	public function registerPidDefinition(string $pidDefinitionClass): LinkAndPidOption {
		return $this->addRegistrationToCachedStack("pids", "main", $pidDefinitionClass);
	}
	
	/**
	 * @inheritDoc
	 */
	public function __applyExtLocalConf() {
		
		// Register pids
		$pids = $this->getCachedStackValueOrRun("pids", PidGenerator::class);
		if (!empty($pids)) {
			$pidAspect = $this->context->TypoContext->getPidAspect();
			foreach ($pids as $k => $pid)
				$pidAspect->setPid($k, $pid);
		}
		
		// Register link sets
		$linkSets = $this->getCachedStackValueOrRun("linkSets", LinkSetGenerator::class);
		if (!empty($linkSets))
			foreach ($linkSets as $k => $linkSet)
				$this->linkSetRepository->set($k, $linkSet);
		
		// Register pid typoScript
		[$ts, $constants] = $this->getCachedValueOrRun("pidTypoScript", function () {
			return $this->context->getInstanceOf(PidTypoScriptGenerator::class)->generate($this->context->TypoContext->getPidAspect()->getAllPids());
		});
		$this->context->TypoScript->addSetup($ts, [
			"constants" => $constants,
			"title"     => "BetterApi - Pid Mapping",
		]);
		
		// Register typoScript hook for updating the pid service when the typoScript template was parsed
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc']["betterApiPid"] = TypoScriptHook::class . "->updatePidService";
	}
}