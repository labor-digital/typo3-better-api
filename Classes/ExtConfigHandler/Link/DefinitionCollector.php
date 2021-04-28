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

namespace LaborDigital\T3BA\ExtConfigHandler\Link;


use LaborDigital\T3BA\ExtConfig\Interfaces\ExtConfigConfiguratorInterface;
use LaborDigital\T3BA\ExtConfig\Interfaces\ExtConfigContextAwareInterface;
use LaborDigital\T3BA\ExtConfig\Traits\ExtConfigContextAwareTrait;
use LaborDigital\T3BA\Tool\Link\Definition;
use LaborDigital\T3BA\Tool\Link\LinkException;
use Neunerlei\Configuration\State\ConfigState;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DefinitionCollector implements ExtConfigConfiguratorInterface, ExtConfigContextAwareInterface
{
    use ExtConfigContextAwareTrait;

    /**
     * The list of registered set definitions
     *
     * @var Definition[]
     */
    protected $definitions = [];

    /**
     * Returns a definition object to configure the link attributes with
     *
     * Note: If another extension already defined the set with the given key the existing instance will be returned!
     * This can be used to override existing link sets
     *
     * @param   string  $key
     *
     * @return \LaborDigital\T3BA\Tool\Link\Definition
     */
    public function getDefinition(string $key): Definition
    {
        if (isset($this->definitions[$key])) {
            return $this->definitions[$key];
        }

        return $this->definitions[$key] = GeneralUtility::makeInstance(Definition::class);
    }

    /**
     * Can be used to check if a set exists or not
     *
     * @param   string  $key
     *
     * @return bool
     */
    public function hasDefinition(string $key): bool
    {
        return isset($this->definitions[$key]);
    }

    /**
     * Can be used to remove a set completely.
     * Becomes useful if you want to completely change an existing set of an another extension
     *
     * @param   string  $key
     *
     * @return $this
     */
    public function removeDefinition(string $key): self
    {
        unset($this->definitions[$key]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function finish(ConfigState $state): void
    {
        $state->set('definitions', array_map('serialize', $this->definitions));

        $state->useNamespace(null, function (ConfigState $state) {
            $state->attachToString('typo.typoScript.pageTsConfig', $this->generateLinkBrowserTsConfig(), true);
        });
    }

    /**
     * Builds the link browser ts config entries for the registered definitions
     *
     * @return string
     * @throws \LaborDigital\T3BA\Tool\Link\LinkException
     */
    protected function generateLinkBrowserTsConfig(): string
    {
        $tsConfig = [];
        foreach ($this->definitions as $key => $linkSet) {
            if ($linkSet->getLinkBrowserConfig() === null) {
                continue;
            }

            $required = $linkSet->getRequiredElements();
            if (count($required) !== 1) {
                throw new LinkException('You can\'t register the link set: "' . $key
                                        . '" to show up in the link browser, because it MUST have EXACTLY ONE required argument or ONE required fragment part!');
            }

            $config  = $linkSet->getLinkBrowserConfig();
            $options = $config['options'];
            $linkSet->clearLinkBrowserConfig();

            $basePid = '';
            if (! empty($options['basePid'])) {
                $basePid = 'storagePid = ' . $this->context->resolvePids($options['basePid']) . PHP_EOL;
            }

            $hidePageTree = '';
            if ((isset($options['hidePageTree']) && $options['hidePageTree'] === true)
                || in_array('hidePageTree', $options, true)) {
                $hidePageTree = 'hidePageTree = 1' . PHP_EOL;
            }

            $tsConfig[] = 'TCEMAIN.linkHandler.linkSet_' . $key . '{' . PHP_EOL .
                          'handler = ' . $config['handler'] . PHP_EOL .
                          'label = ' . $config['label'] . PHP_EOL .
                          'configuration {' . PHP_EOL .
                          'table = ' . $config['table'] . PHP_EOL .
                          'arg = ' . reset($required) . PHP_EOL .
                          $basePid .
                          $hidePageTree .
                          '}' . PHP_EOL .
                          '}' . PHP_EOL;

        }

        return PHP_EOL . implode(PHP_EOL . PHP_EOL, $tsConfig);
    }
}
