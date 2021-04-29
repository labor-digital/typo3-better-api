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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);
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
 * Last modified: 2020.08.23 at 23:23
 */

namespace LaborDigital\T3BA\FormEngine\Addon;

use GuzzleHttp\Psr7\Query;
use LaborDigital\T3BA\Core\Di\StaticContainerAwareTrait;
use LaborDigital\T3BA\Event\FormEngine\BackendFormNodeFilterEvent;
use LaborDigital\T3BA\Event\FormEngine\BackendFormNodePostProcessorEvent;

class FalFileBaseDir
{
    use StaticContainerAwareTrait;
    
    /**
     * This handler adds the baseDir constraints to the javascript of the element browser
     *
     * @param   \LaborDigital\T3BA\Event\FormEngine\BackendFormNodePostProcessorEvent  $event
     */
    public static function onPostProcess(BackendFormNodePostProcessorEvent $event): void
    {
        $config = $event->getProxy()->getConfig();
        if (empty($config['baseDir'])) {
            return;
        }
        
        if (empty($event->getResult()['html'])) {
            return;
        }
        
        // Build the expanded js query so we can tell the js window about our configuration
        $baseDirIdentifier = static::cs()->fal->mkFolder($config['baseDir'])->getCombinedIdentifier();
        $url = '&' . Query::build(['expandFolder' => $baseDirIdentifier]);
        
        // Update the open browser script
        $result = $event->getResult();
        $result['html'] = preg_replace('~(data-params=".*?)(")~si', "$1$url$2", $result['html']);
        $event->setResult($result);
    }
    
    /**
     * This applier is used to allow file relation files to define a "baseDir".
     * The given directory is opened by default if the file browser is opened.
     *
     * @param   \LaborDigital\T3BA\Event\FormEngine\BackendFormNodeFilterEvent  $event
     */
    public static function onNodeFilter(BackendFormNodeFilterEvent $event): void
    {
        // Check inline elements -> default file reference
        $data = $event->getProxy()->getData();
        $config = $event->getProxy()->getConfig();
        $type = 'tca';
        if (! isset($data['renderType']) || $data['renderType'] !== 'inline') {
            // Check for group elements -> For flex form sections
            if (! isset($config['type'], $config['internal_type']) || ! is_array($config)
                || $config['type'] !== 'group' || $config['internal_type'] !== 'file') {
                return;
            }
            $type = 'flex';
        } elseif (! is_array($config) || ! isset($config['foreign_table'])
                  || $config['foreign_table'] !== 'sys_file_reference') {
            return;
        }
        
        // Legacy support
        if (isset($config['rootFolder'])) {
            $config['baseDir'] = $config['rootFolder'];
        }
        
        // Ignore if there is no base dir configured
        if (! isset($config['baseDir'])) {
            return;
        }
        
        // Add the directory path in the session storage
        $session = static::cs()->session->getBackendSession();
        $folders = $session->get('dynamicFalFolders', []);
        
        if ($type === 'tca') {
            $folders[$data['tableName']][$data['fieldName']] = $config['baseDir'];
        } else {
            $folders['flex']['data' . $data['elementBaseName']] = $config['baseDir'];
        }
        
        $session->set('dynamicFalFolders', $folders);
    }
    
    /**
     * This method is used as a default upload folder provider.
     * It will use the stored dynamic fal folders in the session to map the directory browser
     * into the correct fal folder.
     *
     * @param   array  $params
     *
     * @return mixed|\TYPO3\CMS\Core\Resource\Folder
     */
    public function applyConfiguredFalFolders(array $params)
    {
        $folders = static::cs()->session->getBackendSession()->get('dynamicFalFolders', []);
        $request = (string)static::cs()->typoContext->request()->getGet('bparams');
        
        // Check if there was no request -> So we are probably called inline
        if (empty($request)) {
            // Check if we got table and field
            if (! empty($params['table']) && ! empty($params['field'])) {
                if (! isset($folders[$params['table']][$params['field']])) {
                    return $params['uploadFolder'];
                }
                
                $folderDefinition = $folders[$params['table']][$params['field']];
            } else {
                // Nope... this is not what I wanted...
                return $params['uploadFolder'];
            }
        } else {
            // Popup browser
            $request = explode('|', $request);
            
            // Check if we got a flex form field
            if (! empty($request[0]) && empty($request[4])) {
                // Handle flex form
                if (! isset($folders['flex'][$request[0]])) {
                    return $params['uploadFolder'];
                }
                $folderDefinition = $folders['flex'][$request[0]];
            } else {
                // Handle tca field
                if (! isset($request[4])) {
                    return $params['uploadFolder'];
                }
                $request = explode('-', $request[4]);
                if (! isset($request[4])) {
                    return $params['uploadFolder'];
                }
                $table = $request[2];
                if (! isset($folders[$table])) {
                    return $params['uploadFolder'];
                }
                $field = $request[4];
                if (! isset($folders[$table][$field])) {
                    return $params['uploadFolder'];
                }
                $folderDefinition = $folders[$table][$field];
            }
        }
        
        // Get the fal folder
        return static::cs()->fal->mkFolder($folderDefinition);
    }
}
