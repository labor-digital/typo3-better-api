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

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\Links;

use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\CachedStackGeneratorInterface;
use LaborDigital\Typo3BetterApi\Link\LinkException;

class LinkSetGenerator implements CachedStackGeneratorInterface
{

    /**
     * @inheritDoc
     */
    public function generate(array $stack, ExtConfigContext $context, array $additionalData, $option)
    {
        // Skip if there is nothing to do
        if (empty($stack['main'])) {
            return [];
        }

        // Create the collector
        $collector = $context->getInstanceOf(LinkSetCollector::class);

        // Loop through the stack
        $context->runWithCachedValueDataScope($stack['main'],
            function (string $configClass) use ($collector, $context) {
                if (! in_array(LinkSetConfigurationInterface::class, class_implements($configClass))) {
                    throw new ExtConfigException("Invalid link set config class $configClass given. It has to implement the correct interface: "
                                                 . LinkSetConfigurationInterface::class);
                }
                call_user_func([$configClass, 'configureLinkSets'], $collector, $context);
            });

        // Done
        return [
            'definitions' => $linkSets = $collector->__getDefinitions(),
            'tsConfig'    => $this->generateLinkBrowserTsConfig($linkSets, $context),
        ];
    }

    /**
     * @param   \LaborDigital\Typo3BetterApi\Link\LinkSetDefinition[]  $linkSets
     *
     * @return string
     */
    protected function generateLinkBrowserTsConfig(array $linkSets, ExtConfigContext $context): string
    {
        $tsConfig = [];

        foreach ($linkSets as $key => $linkSet) {
            if ($linkSet->getLinkBrowserConfig() === null) {
                continue;
            }

            $requiredArgs = array_filter($linkSet->getArgs(), function ($v) { return $v === '?'; });
            if (count($requiredArgs) !== 1) {
                throw new LinkException('You can\'t register the link set: "' . $key
                                        . '" to show up in the link browser, because it MUST have EXACTLY ONE required argument!');
            }

            $config  = $linkSet->getLinkBrowserConfig();
            $options = $config['options'];
            $linkSet->clearLinkBrowserConfig();

            $tsConfig[] = 'TCEMAIN.linkHandler.linkSet_' . $key . '{' . PHP_EOL .
                          'handler = ' . $config['handler'] . PHP_EOL .
                          'label = ' . $config['label'] . PHP_EOL .
                          'configuration {' . PHP_EOL .
                          'table = ' . $config['table'] . PHP_EOL .
                          (! empty($options['basePid']) ?
                              'storagePid =' . $context->TypoContext->Pid()->get(
                                  $options['basePid'], (int)$options['basePid']
                              ) . PHP_EOL : '') .
                          (in_array('hidePageTree', $options) || $options['hidePageTree'] === true ?
                              'hidePageTree = 1' . PHP_EOL : ''
                          ) .
                          '}' . PHP_EOL;
        }

        return implode(PHP_EOL . PHP_EOL, $tsConfig);
    }
}
