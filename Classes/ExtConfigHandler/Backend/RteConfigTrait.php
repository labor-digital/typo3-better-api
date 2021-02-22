<?php
declare(strict_types=1);
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
 * Last modified: 2020.03.19 at 01:49
 */

namespace LaborDigital\T3BA\ExtConfigHandler\Backend;

use LaborDigital\T3BA\Core\VarFs\Mount;
use Symfony\Component\Yaml\Yaml;

trait RteConfigTrait
{

    /**
     * Processes the given rte config array to match the requirements of TYPO3
     *
     * @param   array  $config   The given configuration to process
     * @param   array  $options  The options to process and merge into the configuration
     *
     * @return array|array[]|\array[][]
     */
    protected function makeRteConfig(array $config, array $options): array
    {
        // Clean up misconfiguration mess
        $c = $config;
        if (! isset($config['editor'])) {
            if (! isset($config['config'])) {
                $c = ['editor' => ['config' => $c]];
            } else {
                $c = ['editor' => ['config' => $config['config']]];
                unset($config['config']);
            }
        } elseif (! isset($config['editor']['config']) && ! isset($config['editor']['stylesSet'])) {
            $c['editor']['config'] = $c['editor'];
        }
        $config = $c;

        // Attach required nodes
        if (! isset($config['imports']) || ! is_array($config['imports'])) {
            $config['imports'] = [];
        }
        if (! isset($config['editor']['config']['stylesSheet'])
            || ! is_array($config['editor']['config']['stylesSheet'])) {
            $config['editor']['config']['stylesSheet'] = [];
        }

        foreach (
            array_merge(
                $options['imports'],
                $options['useDefaultImports'] ? [
                    'EXT:rte_ckeditor/Configuration/RTE/Processing.yaml',
                    'EXT:rte_ckeditor/Configuration/RTE/Editor/Base.yaml',
                    'EXT:rte_ckeditor/Configuration/RTE/Editor/Plugins.yaml',
                ] : []
            ) as $import
        ) {
            if (is_string($import)) {
                $import = ['resource' => $import];
            }

            if (! in_array($import, $config['imports'], true)) {
                $config['imports'][] = $import;
            }
        }

        return $config;
    }

    /**
     * Internal helper to generate a single rte config preset file.
     * The method will dump the content into the tempFs and return the relative path to the file
     *
     * @param   Mount   $mount
     * @param   string  $key
     * @param   array   $config
     *
     * @return string
     */
    protected function dumpRteConfigFile(Mount $mount, string $key, array $config): string
    {
        $fileName = 'rteConfig/' . $key . '.yaml';
        $mount->setFileContent($fileName, Yaml::dump($config));

        return $mount->getFile($fileName)->getPathname();
    }
}
