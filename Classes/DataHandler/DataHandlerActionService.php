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
 * Last modified: 2020.03.21 at 20:48
 */

namespace LaborDigital\Typo3BetterApi\DataHandler;


use LaborDigital\Typo3BetterApi\Event\Events\BackendFormFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\DataHandlerActionPostProcessorEvent;
use LaborDigital\Typo3BetterApi\Event\Events\DataHandlerSaveFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\DataHandlerSavePostProcessorEvent;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use TYPO3\CMS\Core\SingletonInterface;

class DataHandlerActionService implements SingletonInterface, LazyEventSubscriberInterface {
	
	/**
	 * The list of registered data handler handlers by their action stack name
	 * @var array
	 */
	protected $handlers = [];
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionHandlerInterface
	 */
	protected $lazyActionHandler;
	
	/**
	 * DataHandlerActionService constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionHandlerInterface $lazyActionHandler
	 */
	public function __construct(DataHandlerActionHandlerInterface $lazyActionHandler) {
		$this->lazyActionHandler = $lazyActionHandler;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function subscribeToEvents(EventSubscriptionInterface $subscription) {
		$subscription->subscribe(DataHandlerSaveFilterEvent::class, "__runDataHandlerSaveFilters");
		$subscription->subscribe(DataHandlerSavePostProcessorEvent::class, "__runDataHandlerSaveLateFilters");
		$subscription->subscribe(BackendFormFilterEvent::class, "__runBackendFormFilters");
		$subscription->subscribe(DataHandlerActionPostProcessorEvent::class, "__runDataHandlerActionHandlers");
	}
	
	/**
	 * Registers a new action handler on a certain table and action combination.
	 *
	 * @param string $tableName        The table name to register the handler for
	 * @param string $action           The action to register the handler for. "save", "form" or "default"
	 * @param string $handlerClass     The class name of the handler to call when the action is triggered
	 * @param string $handlerMethod    The method name of the handler class to call when the action is triggered
	 * @param array  $fieldConstraints These constraints are an array of field keys and values that have to
	 *                                 match in a table row in order for this service to call the renderer class.
	 *
	 *                                 As an example: If you have a plugin with a signature "mxext_myplugin" and you
	 *                                 are listening for actions on the tt_content table your constraints should look
	 *                                 like: ["CType" => "list", "list_type" => "mxext_myplugin"]. If you want a
	 *                                 renderer for a content element just set the CType ["CType" => "mxext_myplugin"].
	 *                                 If you want to watch for any other value or combination of values... feel free
	 *                                 to be creative... All given fields and values are seen as "AND" constraints
	 *
	 * @return $this
	 */
	public function registerActionHandler(string $tableName, string $action, string $handlerClass, string $handlerMethod, array $fieldConstraints = []) {
		$action = $this->unifyAction($action);
		$handler = $this->makeHandler($id, $handlerClass, $handlerMethod);
		$this->handlers[$tableName][$action][$id] = [
			"handler"     => $handler,
			"constraints" => $fieldConstraints,
		];
		return $this;
	}
	
	/**
	 * Removes a previously registered action handler from the stack.
	 *
	 * @param string $tableName     The table name to remove the handler from
	 * @param string $action        The action to remove the handler from. "save", "form" or "default"
	 * @param string $handlerClass  The name of the handler class to remove
	 * @param string $handlerMethod The name of the action handler method to remove
	 *
	 * @return $this
	 */
	public function removeActionHandler(string $tableName, string $action, string $handlerClass, string $handlerMethod) {
		$action = $this->unifyAction($action);
		$handlers = $this->getHandlersFor($tableName, $action);
		if (empty($handlers)) return $this;
		$this->makeHandler($id, $handlerClass, $handlerMethod);
		unset($this->handlers[$tableName][$action][$id]);
		return $this;
	}
	
	/**
	 * Returns true if a given handler is registered on a certain table and action combination
	 *
	 * @param string $tableName     The table name to check the handlers for
	 * @param string $action        The action name to check the handlers for "save", "saveLate", "form" or "default"
	 * @param string $handlerClass  The handler class name to check for
	 * @param string $handlerMethod The handler method to check for
	 *
	 * @return bool
	 */
	public function hasActionHandler(string $tableName, string $action, string $handlerClass, string $handlerMethod): bool {
		$action = $this->unifyAction($action);
		$this->makeHandler($id, $handlerClass, $handlerMethod);
		$handlers = $this->getHandlersFor($tableName, $action);
		if (empty($handlers)) return FALSE;
		return isset($handlers[$id]);
	}
	
	/**
	 * Returns all registered action handlers of a certain table and action.
	 *
	 * @param string $tableName The table name to find the handlers for
	 * @param string $action    The action name to find the handlers for "save", "form" or "default"
	 *
	 * @return array
	 */
	public function getHandlersFor(string $tableName, string $action): array {
		$action = $this->unifyAction($action);
		return Arrays::getPath($this->handlers, [$tableName, $action], []);
	}
	
	/**
	 * This method is responsible for running all backend save filters that are registered somewhere
	 * in the TCA of the currently saved record.
	 *
	 * @param \LaborDigital\Typo3BetterApi\Event\Events\DataHandlerSaveFilterEvent $event
	 */
	public function __runDataHandlerSaveFilters(DataHandlerSaveFilterEvent $event) {
		$row = $event->getRow();
		$this->lazyActionHandler->runActionStack("save",
			$event->getTableName(), $event->getId(), $event, $row, $isDirty);
		if (!$isDirty) return;
		$event->setRow($row);
	}
	
	/**
	 * This method is responsible for running all backend late save filters (Before the database action but all TCA
	 * rules were applied) that are registered somewhere in the TCA of the currently saved record.
	 *
	 * @param \LaborDigital\Typo3BetterApi\Event\Events\DataHandlerSavePostProcessorEvent $event
	 */
	public function __runDataHandlerSaveLateFilters(DataHandlerSavePostProcessorEvent $event) {
		$row = $event->getRow();
		$this->lazyActionHandler->runActionStack("saveLate",
			$event->getTableName(), $event->getId(), $event, $row, $isDirty);
		if (!$isDirty) return;
		$event->setRow($row);
	}
	
	
	/**
	 * This method is responsible for running all backend form filters that are registered somewhere
	 * in the TCA of the record which's form is currently rendered by the form engine
	 *
	 * @param \LaborDigital\Typo3BetterApi\Event\Events\BackendFormFilterEvent $event
	 */
	public function __runBackendFormFilters(BackendFormFilterEvent $event) {
		$data = $event->getData();
		$row = $data["databaseRow"];
		$this->lazyActionHandler->runActionStack("form", $data["tableName"],
			$data["vanillaUid"], $event, $row, $isDirty);
		if (!$isDirty) return;
		$data["databaseRow"] = $row;
		$event->setData($data);
	}
	
	/**
	 * This method is responsible for running all backend action handlers when the data handler executes
	 * a command, like copying a page. It will scan the target tca for registered handlers and then call them.
	 *
	 * @param \LaborDigital\Typo3BetterApi\Event\Events\DataHandlerActionPostProcessorEvent $event
	 */
	public function __runDataHandlerActionHandlers(DataHandlerActionPostProcessorEvent $event) {
		// Skip if there is something clearly wrong
		// This may happen if the copy of an entry failed...
		$uid = ($event->getNewId() >= 0) ? $event->getNewId() : $event->getId();
		if (!is_numeric($uid) || $uid < 0) return;
		
		// Start the normal execution
		$row = [];
		$this->lazyActionHandler->runActionStack("action", $event->getTableName(), $uid, $event, $row, $isDirty);
		
		// This is tricky... We will inject our changed row into the "pastDataMap" which then will be processed
		// by the copy TCE data handler after this command finished running.
		if (!$isDirty || empty($row)) return;
		
		// Update the past data map
		$dataMap = $event->getPasteDataMap();
		$old = Arrays::getPath($dataMap, [$event->getTableName()], []);
		unset($row["uid"]);
		$dataMap[$event->getTableName()] = Arrays::merge($old, [$uid => $row]);
		$event->setPasteDataMap($dataMap);
	}
	
	/**
	 * Internal helper to allow only allowed actions to pass...
	 *
	 * @param string $action
	 *
	 * @return string
	 */
	protected function unifyAction(string $action): string {
		$action = strtolower(trim($action));
		if ($action === "save" || $action === "form") return $action;
		return "default";
	}
	
	/**
	 * Internal helper to create the helper class name and array representation
	 *
	 * @param        $id
	 * @param string $handlerClass
	 * @param string $handlerMethod
	 *
	 * @return array
	 */
	protected function makeHandler(&$id, string $handlerClass, string $handlerMethod): array {
		$id = $handlerClass . "->" . $handlerMethod;
		return [$handlerClass, $handlerMethod];
	}
	
}