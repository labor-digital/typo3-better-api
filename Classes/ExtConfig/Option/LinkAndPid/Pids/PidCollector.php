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

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\Pids;


use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use Neunerlei\Arrays\Arrays;

class PidCollector {
	
	/**
	 * The list of configured pids
	 * @var array
	 */
	protected $pids = [];
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
	 */
	protected $context;
	
	/**
	 * PidCollector constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext $context
	 */
	public function __construct(ExtConfigContext $context) {
		$this->context = $context;
	}
	
	/**
	 * Adds a new pid mapping to the pid service.
	 *
	 * @param string $key      A key like "myKey", "$pid.storage.stuff" or "storage.myKey" for hierarchical data
	 * @param int    $pid      The numeric page id which should be returned when the given pid is required
	 * @param bool   $override By default we will not override pid's set by typoscript. If you want to do so set this
	 *                         to true.
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\Pids\PidCollector
	 */
	public function set(string $key, int $pid, bool $override = FALSE): PidCollector {
		// Set the pid, or ignore it if it exists and we should not override it
		$key = $this->context->replaceMarkers($key);
		if (isset($this->pids[$key]) && !$override) return $this;
		$this->pids[$key] = $pid;
		return $this;
	}
	
	/**
	 * The same as registerPid() but registers multiple pids at once
	 *
	 * @param array $pids      A list of pids as $path => $pid or as multidimensional array
	 * @param bool  $override  By default we will not override pids set by typoscript. If you want to do so set this
	 *                         to true.
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\Pids\PidCollector
	 * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
	 */
	public function setMultiple(array $pids, bool $override = FALSE): PidCollector {
		foreach (Arrays::flatten($pids) as $k => $pid) {
			if (!is_string($k)) throw new ExtConfigException("The given key for pid: " . $pid . " has to be a string!");
			if (!is_numeric($pid)) throw new ExtConfigException("The given value for pid: " . $k . " has to be numeric! Given value: " . $pid);
			$this->set($k, (int)$pid, $override);
		}
		return $this;
	}
	
	/**
	 * Returns all registered pid's in this collector instance
	 * @return array
	 */
	public function getAll(): array {
		return $this->pids;
	}
}