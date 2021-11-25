<?php
/*
 * Copyright 2021 LABOR.digital
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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);

namespace LaborDigital\T3ba\ExtConfigHandler\Link;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\Interfaces\ExtConfigConfiguratorInterface;
use LaborDigital\T3ba\ExtConfig\Interfaces\ExtConfigContextAwareInterface;
use LaborDigital\T3ba\ExtConfig\Traits\ExtConfigContextAwareTrait;
use LaborDigital\T3ba\Tool\Link\Definition;
use LaborDigital\T3ba\Tool\Link\LinkException;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\State\ConfigState;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DefinitionCollector implements ExtConfigConfiguratorInterface, ExtConfigContextAwareInterface, NoDiInterface
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
     * @return \LaborDigital\T3ba\Tool\Link\Definition
     */
    public function getDefinition(string $key): Definition
    {
        return $this->definitions[$key] ?? ($this->definitions[$key] = GeneralUtility::makeInstance(Definition::class));
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
     * @throws \LaborDigital\T3ba\Tool\Link\LinkException
     */
    protected function generateLinkBrowserTsConfig(): string
    {
        $tsConfig = [];
        foreach ($this->definitions as $key => $linkSet) {
            if ($linkSet->getLinkBrowserConfig() === null) {
                continue;
            }
            
            $requiredElements = $linkSet->getRequiredElements();
            if (count($requiredElements) !== 1) {
                throw new LinkException('You can\'t register the link set: "' . $key
                                        . '" to show up in the link browser, because it MUST have EXACTLY ONE required argument or ONE required fragment part!');
            }
            
            $config = $linkSet->getLinkBrowserConfig();
            $linkSet->clearLinkBrowserConfig();
            
            $tsConfig[] = $this->buildLinkHandlerTypoScript($key, $config, $requiredElements);
        }
        
        return PHP_EOL . implode(PHP_EOL . PHP_EOL, $tsConfig);
    }
    
    /**
     * Generates the main typoScript declaration to register the link handler in the TS config array
     *
     * @param   string  $key
     * @param   array   $config
     * @param   array   $requiredElements
     *
     * @return string
     */
    protected function buildLinkHandlerTypoScript(string $key, array $config, array $requiredElements): string
    {
        $options = $config['options'];
        $ts = 'TCEMAIN.linkHandler.linkSet_' . $key . '{' . PHP_EOL .
              'handler = ' . $config['handler'] . PHP_EOL .
              'label = ' . $config['label'] . PHP_EOL .
              'configuration {' . PHP_EOL .
              'table = ' . $config['table'] . PHP_EOL .
              'arg = ' . reset($requiredElements) . PHP_EOL .
              $this->buildStoragePidDefinition($options) .
              $this->buildHidePageTreeDefinition($options) .
              '}' . PHP_EOL .
              '}' . PHP_EOL;
        
        return $this->buildLimitToSitesWrap($ts, $options);
    }
    
    /**
     * Generates the "storagePid" declaration for the linkHandler TypoScript
     *
     * @param   array  $options
     *
     * @return string|null
     */
    protected function buildStoragePidDefinition(array $options): ?string
    {
        if (! empty($options['basePid'])) {
            if (is_array($options['basePid'])) {
                $storagePidOptions = [];
                foreach ($options['basePid'] as $siteIdentifier => $pid) {
                    $storagePidOptions[] = '[betterSite("identifier") == "' . $siteIdentifier . '"]' . PHP_EOL .
                                           'storagePid = ' . $pid . PHP_EOL
                                           . '[END]';
                }
                
                return implode(PHP_EOL, $storagePidOptions);
            }
            
            return 'storagePid = ' . $options['basePid'] . PHP_EOL;
        }
        
        return null;
    }
    
    /**
     * Builds the "hidePageTree" declaration for the linkHandler TypoScript
     *
     * @param   array  $options
     *
     * @return string|null
     */
    protected function buildHidePageTreeDefinition(array $options): ?string
    {
        if ((isset($options['hidePageTree']) && $options['hidePageTree'] === true)
            || in_array('hidePageTree', $options, true)) {
            return 'hidePageTree = 1' . PHP_EOL;
        }
        
        return null;
    }
    
    /**
     * Wraps the given $ts string into a condition that only applies to specific sites
     *
     * @param   string  $ts
     * @param   array   $options
     *
     * @return string
     */
    protected function buildLimitToSitesWrap(string $ts, array $options): string
    {
        if (! empty($options['limitToSites'])) {
            $sites = is_string($options['limitToSites'])
                ? Arrays::makeFromStringList($options['limitToSites'])
                : null ?? (is_array($options['limitToSites']) ? $options['limitToSites'] : []);
            
            $conditions = array_map(static function ($v): string {
                return 'betterSite("identifier") == "' . $v . '"';
            }, $sites);
            
            $ts = '[' . implode(' || ', $conditions) . ']' . PHP_EOL .
                  $ts . PHP_EOL
                  . '[END]';
        }
        
        return $ts;
    }
}
