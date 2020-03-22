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
 * Last modified: 2020.03.20 at 14:07
 */

namespace LaborDigital\Typo3BetterApi\Rendering;

use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\Event\Events\BackendDbListQueryFilterEvent;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\EventBus\EventBusInterface;
use Neunerlei\Options\Options;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;

class BackendRenderingService implements SingletonInterface {
	
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
	 */
	protected $container;
	
	/**
	 * @var \Neunerlei\EventBus\EventBusInterface
	 */
	protected $eventBus;
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
	 */
	protected $context;
	
	/**
	 * BackendRenderingService constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\TypoContext\TypoContext          $context
	 * @param \Neunerlei\EventBus\EventBusInterface                         $eventBus
	 * @param \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface $container
	 */
	public function __construct(TypoContext $context, EventBusInterface $eventBus, TypoContainerInterface $container) {
		$this->eventBus = $eventBus;
		$this->container = $container;
		$this->context = $context;
	}
	
	/**
	 * This method can be used to render a database record list in the backend.
	 * The process is normally quite painful but with this interface it should become fairly easy.
	 *
	 * @param string $table   The table of which you want to render a database table
	 * @param array  $fields  An array of columns that should be read from the database
	 * @param array  $options Additional options to configure the output
	 *                        - limit int (20): The max number of items to display
	 *                        - where string: A MYSQL query string beginning at "SELECT ... WHERE " <- your string
	 *                        starts here
	 *                        - pid int ($CURRENT_PID): The page id to limit the items to.
	 *                        - callback callable: This can be used to change or extend the default
	 *                        settings of the list renderer. The callback receives the preconfigured
	 *                        instance as parameter right before the list is rendered.
	 *
	 *
	 * @return string
	 */
	public function renderDatabaseRecordList(string $table, array $fields, array $options = []): string {
		// Prepare the options
		$options = Options::make($options, [
			"limit"    => [
				"type"    => "int",
				"default" => 20,
			],
			"pid"      => [
				"type"    => "int",
				"default" => function () {
					return $this->context->getPidAspect()->getCurrentPid();
				},
			],
			"where"    => [
				"type"    => "string",
				"default" => "",
			],
			"callback" => [
				"type"    => ["callable", "null"],
				"default" => NULL,
			],
		]);
		
		// Prepare object
		$pid = $options["pid"];
		$pageInfo = BackendUtility::readPageAccess($options["pid"], "");
		/** @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $backendUser */
		$backendUser = $GLOBALS["BE_USER"];
		
		/** @var DatabaseRecordList $dbList */
		$dbList = $this->container->get(DatabaseRecordList::class, ["gu" => TRUE]);
		$dbList->script = GeneralUtility::getIndpEnv("REQUEST_URI");
		$dbList->thumbs = $backendUser->uc["thumbnailsByDefault"];
		$dbList->allFields = 1;
		$dbList->clickTitleMode = "edit";
		$dbList->calcPerms = $backendUser->calcPerms($pageInfo);
		$dbList->showClipboard = 0;
		$dbList->disableSingleTableView = 1;
		$dbList->pageRow = $pageInfo;
		$dbList->displayFields = FALSE;
		$dbList->dontShowClipControlPanels = TRUE;
		$dbList->counter++;
		
		$pointer = MathUtility::forceIntegerInRange($this->context->getRequestAspect()->getGet("pointer"), 0);
		$dbList->start($pid, $table, $pointer, "", 0, $options["limit"]);
		$dbList->script = $_SERVER["REQUEST_URI"];
		$dbList->setDispFields();
		
		// Apply the field list filter
		if (!empty($fields)) $dbList->setFields = [$table => $fields];
		
		// Trigger the callback if we have one
		if (is_callable($options["callback"])) call_user_func($options["callback"], $dbList);
		
		// Register the event handler for injecting our additional where clause
		if (!empty($options["where"])) {
			$emitted = FALSE;
			$this->eventBus->addListener(BackendDbListQueryFilterEvent::class,
				function (BackendDbListQueryFilterEvent $event) use (&$emitted, $options) {
					// Skip if the event was already emitted
					if ($emitted) return;
					$emitted = TRUE;
					
					// Inject our where statement
					$whereParts = explode(' OR ', $event->getAdditionalWhereClause());
					$event->setAdditionalWhereClause(implode(" " . $options["where"] . " OR ", $whereParts) . " " . $options["where"]);
					
					// Move all pseudo fields to the right...
					$fieldArray = $event->getListRenderer()->fieldArray;
					$fieldArrayFiltered = array_filter($fieldArray, function ($v) { return $v[0] !== '_'; });
					$fieldArrayFiltered = $fieldArrayFiltered + array_filter($fieldArray, function ($v) { return $v[0] === '_'; });
					$event->getListRenderer()->fieldArray = array_values($fieldArrayFiltered);
				});
		}
		
		// Generate the list
		$dbList->generateList();
		
		// Check for empty response
		if ($dbList->totalItems === 0) return "";
		
		// Append T3 location
		$result = $dbList->HTMLcode;
		$requestUrl = GeneralUtility::quoteJSvalue(rawurlencode(GeneralUtility::getIndpEnv("REQUEST_URI")));
		/** @noinspection JSUnresolvedVariable */
		$result .= "<script type=\"text/javascript\">if(typeof T3_THIS_LOCATION === 'undefined') T3_THIS_LOCATION = " . $requestUrl . "; </script>";
		
		// Done
		return $result;
	}
}