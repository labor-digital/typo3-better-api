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


namespace LaborDigital\T3ba\Tool\Rendering\Renderer;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Event\Backend\DbListQueryFilterEvent;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\Options\Options;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;

class DatabaseRecordListRenderer implements PublicServiceInterface
{
    use ContainerAwareTrait;
    use TypoContextAwareTrait;
    
    /**
     * True if the record filter event was registered, false if not
     *
     * @var bool
     */
    protected $dbRecordFilterRegistered = false;
    
    /**
     * Holds the data for the database record list renderer
     *
     * @var array
     */
    protected $dbRecordFilterTmp = [];
    
    /**
     * Renders a complex TYPO3 backend list for a database table
     *
     * @param   string|mixed  $tableName
     * @param   array         $fields
     * @param   array         $options
     *
     * @return string
     * @see \LaborDigital\T3ba\Tool\Rendering\BackendRenderingService::renderDatabaseRecordList() for a description
     */
    public function render($tableName, array $fields, array $options = []): string
    {
        $tableName = NamingUtil::resolveTableName($tableName);
        
        $options = $this->validateOptions($options);
        $dbList = $this->prepareConcreteRenderer($tableName, $options);
        
        if (! empty($fields)) {
            $dbList->setFields = [$tableName => $fields];
        }
        
        if (is_callable($options['callback'])) {
            call_user_func($options['callback'], $dbList);
        }
        
        if (! empty($options['where'])) {
            $this->handleWhereExtension($options);
        }
        
        $dbList->generateList();
        $this->dbRecordFilterTmp = [];
        
        if ((int)$dbList->totalItems === 0) {
            return '';
        }
        
        // Append T3 location
        $result = $dbList->HTMLcode;
        $requestUrl = GeneralUtility::quoteJSvalue(rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI')));
        /** @noinspection JSUnresolvedVariable */
        $result .= '<script type="text/javascript">if(typeof T3_THIS_LOCATION === \'undefined\') T3_THIS_LOCATION = '
                   . $requestUrl . '; </script>';
        
        return $result;
    }
    
    /**
     * Validates and prepares the options provided to the renderer
     *
     * @param   array  $options
     *
     * @return array
     */
    protected function validateOptions(array $options): array
    {
        return Options::make($options, [
            'limit' => [
                'type' => 'int',
                'default' => 20,
            ],
            'pid' => [
                'type' => 'int',
                'default' => function () {
                    return $this->getTypoContext()->pid()->getCurrent();
                },
            ],
            'where' => [
                'type' => 'string',
                'default' => '',
            ],
            'callback' => [
                'type' => ['callable', 'null'],
                'default' => null,
            ],
        ]);
    }
    
    /**
     * Creates the concrete record list renderer instance based on the given options
     *
     * @param   string  $tableName
     * @param   array   $options
     *
     * @return \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList
     */
    protected function prepareConcreteRenderer(string $tableName, array $options): DatabaseRecordList
    {
        $pid = $options['pid'];
        $pageInfo = BackendUtility::readPageAccess($options['pid'], '');
        /** @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $backendUser */
        $backendUser = $GLOBALS['BE_USER'];
        $requestUri = GeneralUtility::getIndpEnv('REQUEST_URI');
        
        /** @var \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList $dbList */
        $dbList = $this->makeInstance(DatabaseRecordList::class);
        $dbList->script = $requestUri;
        $dbList->thumbs = $backendUser->uc['thumbnailsByDefault'];
        $dbList->allFields = 1;
        $dbList->clickTitleMode = 'edit';
        $dbList->calcPerms = $backendUser->calcPerms($pageInfo);
        $dbList->showClipboard = 0;
        $dbList->disableSingleTableView = 1;
        $dbList->pageRow = $pageInfo;
        $dbList->displayFields = false;
        $dbList->dontShowClipControlPanels = true;
        $dbList->counter++;
        
        $pointer = MathUtility::forceIntegerInRange($this->getTypoContext()->request()->getGet('pointer'), 0);
        $dbList->start($pid, $tableName, $pointer, '', 0, $options['limit']);
        $dbList->script = $requestUri;
        $dbList->setDispFields();
        
        return $dbList;
    }
    
    /**
     * Registers a dynamic event handler when the DbListQueryFilterEvent is emitted.
     * This allows this renderer to update the where statement based on the provided options.
     *
     * @param   array  $options
     */
    protected function handleWhereExtension(array $options): void
    {
        if (! $this->dbRecordFilterRegistered) {
            $this->dbRecordFilterRegistered = true;
            
            $this->getService(TypoEventBus::class)->addListener(
                DbListQueryFilterEvent::class, function (DbListQueryFilterEvent $event) {
                // Skip if the event was already emitted
                if ($this->dbRecordFilterTmp['emitted'] || empty($this->dbRecordFilterTmp['options'])) {
                    return;
                }
                $this->dbRecordFilterTmp['emitted'] = true;
                $options = $this->dbRecordFilterTmp['options'];
                
                // Inject our where statement
                $whereParts = explode(' OR ', $event->getAdditionalWhereClause());
                /** @noinspection ImplodeMissUseInspection */
                $event->setAdditionalWhereClause(
                    implode(' ' . $options['where'] . ' OR ', $whereParts) . ' ' . $options['where']
                );
                
                // Move all pseudo fields to the right...
                $fieldArray = $event->getListRenderer()->fieldArray;
                $fieldArrayFiltered = array_filter($fieldArray,
                    static function ($v) {
                        return $v[0] !== '_';
                    });
                $fieldArrayFiltered += array_filter($fieldArray,
                    static function ($v) {
                        return $v[0] === '_';
                    });
                
                $event->getListRenderer()->fieldArray = array_values($fieldArrayFiltered);
            });
        }
        
        // Set our options
        $this->dbRecordFilterTmp = [
            'emitted' => false,
            'options' => $options,
        ];
    }
}