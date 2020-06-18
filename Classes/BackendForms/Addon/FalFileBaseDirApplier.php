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
 * Last modified: 2020.03.18 at 16:30
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\Addon;

use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodeFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodePostProcessorEvent;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use function GuzzleHttp\Psr7\build_query;

class FalFileBaseDirApplier implements LazyEventSubscriberInterface
{
    use CommonServiceLocatorTrait;
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(BackendFormNodeFilterEvent::class, '__onNodeDataFilter');
        $subscription->subscribe(BackendFormNodePostProcessorEvent::class, '__onPostProcess');
        
        // Register ourselves as fal folder handler
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['getDefaultUploadFolder'][static::class]
            = static::class . '->__applyConfiguredFalFolders';
    }
    
    /**
     * This handler adds the baseDir constraints to the javascript of the element browser
     *
     * @param   \LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodePostProcessorEvent  $event
     */
    public function __onPostProcess(BackendFormNodePostProcessorEvent $event)
    {
        // Ignore if there is nothing to filter...
        if (empty($event->getResult()['html'])) {
            return;
        }
        $config = Arrays::getPath($event->getProxy()->getProperty('data'), ['parameterArray', 'fieldConf', 'config'],
            []);
        
        // Check if there is work for us to do
        if (is_null($config['baseDir'])) {
            return;
        }
        
        // Build the expanded js query so we can tell the js window about our configuration
        $baseDirIdentifier = $this->FalFiles->mkFolder($config['baseDir'])->getCombinedIdentifier();
        $url               = '&' . build_query(['expandFolder' => $baseDirIdentifier]);
        
        // Update the open browser script
        $result         = $event->getResult();
        $result['html'] = preg_replace("~(setFormValueOpenBrowser\('file'[^\"]*?)('\);\s?return false;)~si", "$1$url$2",
            $result['html']);
        $event->setResult($result);
    }
    
    /**
     * This applier is used to allow file relation files to define a "baseDir".
     * The given directory is opened by default if the file browser is opened.
     *
     * @param   \LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodeFilterEvent  $event
     */
    public function __onNodeDataFilter(BackendFormNodeFilterEvent $event)
    {
        $data = $event->getProxy()->getProperty('data');
        
        // Check inline elements -> default file reference
        $c    = Arrays::getPath($data, 'parameterArray.fieldConf.config');
        $type = 'tca';
        if (! isset($data['renderType']) || $data['renderType'] !== 'inline') {
            // Check for group elements -> For flex form sections
            if (! is_array($c) || ! isset($c['type']) || $c['type'] !== 'group' || ! isset($c['internal_type'])
                || $c['internal_type'] !== 'file') {
                return;
            }
            $type = 'flex';
        } else {
            // Handle inline elements
            if (! is_array($c) || ! isset($c['foreign_table']) || $c['foreign_table'] !== 'sys_file_reference') {
                return;
            }
        }
        
        // Legacy support
        if (isset($c['rootFolder'])) {
            $c['baseDir'] = $c['rootFolder'];
        }
        
        // Ignore if there is no base dir configured
        if (! isset($c['baseDir'])) {
            return;
        }
        
        // Add the directory path in the session storage
        $folders = $this->Session->getBackendSession()->get('dynamicFalFolders', []);
        if ($type === 'tca') {
            $folders[$data['tableName']][$data['fieldName']] = $c['baseDir'];
        } else {
            $folders['flex']['data' . $data['elementBaseName']] = $c['baseDir'];
        }
        $this->Session->getBackendSession()->set('dynamicFalFolders', $folders);
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
    public function __applyConfiguredFalFolders(array $params)
    {
        $folders = $this->Session->getBackendSession()->get('dynamicFalFolders', []);
        $request = (string)$this->TypoContext->getRequestAspect()->getGet('bparams');
        
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
        return $this->FalFiles->mkFolder($folderDefinition);
    }
}
