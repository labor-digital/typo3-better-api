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
 * Last modified: 2020.03.20 at 14:05
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter;


use LaborDigital\Typo3BetterApi\Event\Events\BackendDbListQueryFilterEvent;
use TYPO3\CMS\Backend\RecordList\RecordListGetTableHookInterface;

class BackendDbListQueryFilterEventAdapter extends AbstractCoreHookEventAdapter implements RecordListGetTableHookInterface {
	
	/**
	 * @inheritDoc
	 */
	public static function bind(): void {
		$GLOBALS["TYPO3_CONF_VARS"]["SC_OPTIONS"]["typo3/class.db_list_extra.inc"]["getTable"]
		[static::class] = static::class;
	}
	
	public function getDBlistQuery($table, $pageId, &$additionalWhereClause, &$selectedFieldsList, &$parentObject) {
		if (!static::$context->getEnvAspect()->isBackend()) return;
		static::$bus->dispatch(($e = new BackendDbListQueryFilterEvent(
			$table, $pageId, $additionalWhereClause, $selectedFieldsList, $parentObject
		)));
		$additionalWhereClause = $e->getAdditionalWhereClause();
		$selectedFieldsList = $e->getSelectedFieldList();
		$parentObject = $e->getListRenderer();
	}
}