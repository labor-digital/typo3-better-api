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
 * Last modified: 2020.03.19 at 01:26
 */

namespace LaborDigital\Typo3BetterApi\Session;

use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class FrontendSessionProvider implements SessionInterface, SingletonInterface {
	
	/**
	 * @inheritDoc
	 */
	public function has(string $path): bool {
		return Arrays::hasPath($this->getSessionValues(), $path);
	}
	
	/**
	 * @inheritDoc
	 */
	public function get(string $path = NULL, $default = NULL) {
		$values = $this->getSessionValues();
		if ($path === NULL) return $values;
		return Arrays::getPath($values, $path, $default);
	}
	
	/**
	 * @inheritDoc
	 */
	public function set(string $path, $value) {
		$feUser = $this->getFeUser();
		if (empty($feUser)) return $this;
		$values = $this->getSessionValues();
		$values = Arrays::setPath($values, $path, $value);
		$feUser->setAndSaveSessionData("LaborTypo3BetterApi", $values);
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function remove(string $path) {
		$feUser = $this->getFeUser();
		if (empty($feUser)) return $this;
		$values = $this->getSessionValues();
		$values = Arrays::removePath($values, $path);
		$feUser->setAndSaveSessionData("LaborTypo3BetterApi", $values);
		return $this;
	}
	
	/**
	 * Helper to retrieve the session values from typo3
	 * @return array
	 */
	protected function getSessionValues(): array {
		$feUser = $this->getFeUser();
		if (empty($feUser)) return [];
		$value = $feUser->getKey("ses", "LaborTypo3BetterApi");
		return is_array($value) ? $value : [];
	}
	
	/**
	 * Helper to get the instance of the typo3 frontend user
	 * @return FrontendUserAuthentication|null
	 */
	protected function getFeUser() {
		if (empty($GLOBALS["TSFE"]) || !$GLOBALS["TSFE"] instanceof TypoScriptFrontendController) return NULL;
		if (empty($GLOBALS["TSFE"]->fe_user) || !$GLOBALS["TSFE"]->fe_user instanceof FrontendUserAuthentication) return NULL;
		return $GLOBALS["TSFE"]->fe_user;
	}
}