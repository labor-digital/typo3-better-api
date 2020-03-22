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
 * Last modified: 2020.03.18 at 19:36
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig;


use LaborDigital\Typo3BetterApi\BetterApiException;
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Service\DependencyOrderingService;

class ConfigSorter {
	/**
	 * This method can be used to sort a list of elements by dependencies.
	 * It uses Typo3's dependency ordering service internally, but handles some quirks it has to make
	 * it easier to use and understand.
	 *
	 * Your list should look like ["a" => ["before" => "foo", "after" => ["foo", "bar"], "b" => ["before" => ...]]
	 *
	 * "before" defines either a single, or multiple elements that should be after this element.
	 * "after" does the opposite and defines elements that should come before this element.
	 * Both keys can either be an array or a single key value.
	 *
	 * There are two "special" keys. "start" and "end". You can do ["a" => ["before" => "end"] ["b" = ...]] to
	 * always put this element to the end of the list. Using the "start" key does the opposite. You can always
	 * use "after" => "end" and "before" => "start" which will be sorted with higher priority than "before" => "end"
	 * and "after" => "start".
	 *
	 * @param array $list
	 *
	 * @return array
	 * @throws \LaborDigital\Typo3BetterApi\BetterApiException
	 */
	public static function sortByDependencies(array $list): array {
		// Prepare the storage list
		$listClean = [
			"first" => [],
			"last"  => ["after" => "first"],
		];
		
		// First pass apply the options
		foreach ($list as $k => $v) {
			if (empty($v)) continue;
			if (!is_array($v)) throw new BetterApiException("The given list contains an element at key: $k which is not an array!");
			
			// Prepare options
			$_v = Options::make($v, [
				"before" => [
					"default" => [],
					"type"    => ["string", "array"],
					"filter"  => function ($v) {
						return !is_array($v) ? [$v] : $v;
					},
				],
				"after"  => [
					"default" => [],
					"type"    => ["string", "array"],
					"filter"  => function ($v) {
						return !is_array($v) ? [$v] : $v;
					},
				],
			], ["allowUnknown" => TRUE]);
			
			// Remap before and after as they seem to work counter intuitive.
			$_v["@sortVarsVanilla"] = [$_v["before"], $_v["after"]];
			$tmp = $_v["before"];
			$_v["before"] = $_v["after"];
			$_v["after"] = $tmp;
			
			// Add it to the list
			$listClean[$k] = $_v;
		}
		
		// Sort the list
		$listSorted = TypoContainer::getInstance()->get(DependencyOrderingService::class)->orderByDependencies($listClean);
		
		// Second pass, revert the remapped before/after keys
		foreach ($listSorted as &$v) {
			if (!isset($v["@sortVarsVanilla"])) continue;
			$v["before"] = $v["@sortVarsVanilla"][0];
			$v["after"] = $v["@sortVarsVanilla"][1];
			unset($v["@sortVarsVanilla"]);
		}
		
		// Clean the list
		$listSorted = array_reverse($listSorted);
		unset($listSorted["first"]);
		unset($listSorted["last"]);
		
		// Done
		return $listSorted;
	}
}