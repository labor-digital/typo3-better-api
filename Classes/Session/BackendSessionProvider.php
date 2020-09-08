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
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;

class BackendSessionProvider implements SessionInterface, SingletonInterface
{
    
    /**
     * @inheritDoc
     */
    public function has(string $path): bool
    {
        return Arrays::hasPath($this->getSessionValues(), $path);
    }
    
    /**
     * @inheritDoc
     */
    public function get(string $path = null, $default = null)
    {
        $values = $this->getSessionValues();
        if ($path === null) {
            return $values;
        }
        
        return Arrays::getPath($values, $path, $default);
    }
    
    /**
     * @inheritDoc
     */
    public function set(string $path, $value)
    {
        $beUser = $this->getBeUser();
        if (empty($beUser)) {
            return $this;
        }
        $values = $this->getSessionValues();
        $values = Arrays::setPath($values, $path, $value);
        $beUser->setAndSaveSessionData('LaborTypo3BetterApi', $values);
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function remove(string $path)
    {
        $beUser = $this->getBeUser();
        if (empty($beUser)) {
            return $this;
        }
        $values = $this->getSessionValues();
        $values = Arrays::removePath($values, $path);
        $beUser->setAndSaveSessionData('LaborTypo3BetterApi', $values);
        
        return $this;
    }
    
    /**
     * Helper to retrieve the stored data from the session
     *
     * @return array
     */
    protected function getSessionValues()
    {
        $beUser = $this->getBeUser();
        if (empty($beUser)) {
            return [];
        }
        $value = $beUser->getSessionData('LaborTypo3BetterApi');
        
        return is_array($value) ? $value : [];
    }
    
    /**
     * Helper to get the backend user instance
     *
     * @return FrontendBackendUserAuthentication|null
     */
    protected function getBeUser()
    {
        return isset($GLOBALS['BE_USER']) ? $GLOBALS['BE_USER'] : null;
    }
}