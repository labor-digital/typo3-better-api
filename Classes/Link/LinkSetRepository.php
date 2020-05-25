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

namespace LaborDigital\Typo3BetterApi\Link;

use TYPO3\CMS\Core\SingletonInterface;

class LinkSetRepository implements SingletonInterface
{
    
    /**
     * @var \LaborDigital\Typo3BetterApi\Link\LinkSetDefinition[]
     */
    protected $linkSets = [];
    
    /**
     * Returns all currently registered link sets
     * @return array
     */
    public function getAll(): array
    {
        return $this->linkSets;
    }
    
    /**
     * Returns true if the set with the given key exists, false if not
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->linkSets[$key]);
    }
    
    /**
     * Returns the configuration for a certain link set's key if it exists
     *
     * @param string $key
     *
     * @return \LaborDigital\Typo3BetterApi\Link\LinkSetDefinition
     * @throws \LaborDigital\Typo3BetterApi\Link\LinkException
     */
    public function get(string $key): LinkSetDefinition
    {
        if (!$this->has($key)) {
            throw new LinkException('The requested link set with key: "' . $key . '" was not found!');
        }
        return $this->linkSets[$key];
    }
    
    /**
     * Sets the definition of a link set with a given key
     *
     * A link set is basically the same as a link but can hold "placeholders" and are reusable.
     * If the link does not contain all required arguments (which you defined as placeholders) an exception is thrown.
     *
     * @param string            $key        The identifier of your link set
     * @param LinkSetDefinition $definition The definition to set for this link set
     *
     * @return \LaborDigital\Typo3BetterApi\Link\LinkSetRepository
     */
    public function set(string $key, LinkSetDefinition $definition): LinkSetRepository
    {
        $this->linkSets[$key] = $definition;
        return $this;
    }
}
