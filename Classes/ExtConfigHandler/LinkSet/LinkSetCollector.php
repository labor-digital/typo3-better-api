<?php
/*
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
 * Last modified: 2020.08.23 at 23:23
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\ExtConfigHandler\LinkSet;


use LaborDigital\T3BA\ExtConfig\ExtConfigConfiguratorInterface;
use LaborDigital\T3BA\Tool\Link\LinkSetDefinition;
use Neunerlei\Configuration\State\ConfigState;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LinkSetCollector implements ExtConfigConfiguratorInterface
{

    /**
     * The list of registered set definitions
     *
     * @var array
     */
    protected $definitions = [];

    /**
     * Returns a link set definition object you may use to define the link set
     *
     * Note: If another extension already defined the set with the given key the existing instance will be returned!
     * This can be used to override existing link sets
     *
     * @param   string  $key
     *
     * @return \LaborDigital\T3BA\Tool\Link\LinkSetDefinition
     */
    public function getSet(string $key): LinkSetDefinition
    {
        if (isset($this->definitions[$key])) {
            return $this->definitions[$key];
        }

        return $this->definitions[$key] = GeneralUtility::makeInstance(LinkSetDefinition::class);
    }

    /**
     * Can be used to check if a set exists or not
     *
     * @param   string  $key
     *
     * @return bool
     */
    public function hasSet(string $key): bool
    {
        return isset($this->definitions[$key]);
    }

    /**
     * Can be used to remove a set completely.
     * Becomes useful if you want to completely change an existing set of an another extension
     *
     * @param   string  $key
     *
     * @return \LaborDigital\T3BA\ExtConfigHandler\LinkSet\LinkSetCollector
     */
    public function removeSet(string $key): self
    {
        unset($this->definitions[$key]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function finish(ConfigState $state): void
    {
        $state->set('sets', array_map('serialize', $this->definitions));
    }
}
