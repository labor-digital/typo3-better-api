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
 * Last modified: 2020.03.20 at 14:07
 */

namespace LaborDigital\T3BA\Tool\Rendering;

use LaborDigital\T3BA\Core\DependencyInjection\ContainerAwareTrait;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Event\Backend\DbListQueryFilterEvent;
use LaborDigital\T3BA\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\Options\Options;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;

class BackendRenderingService implements SingletonInterface
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
     * This method can be used to render a database record list in the backend.
     * The process is normally quite painful but with this interface it should become fairly easy.
     *
     * @param   string  $table    The table of which you want to render a database table
     * @param   array   $fields   An array of columns that should be read from the database
     * @param   array   $options  Additional options to configure the output
     *                            - limit int (20): The max number of items to display
     *                            - where string: A MYSQL query string beginning at "SELECT ... WHERE " <- your string
     *                            starts here
     *                            - pid int ($CURRENT_PID): The page id to limit the items to.
     *                            - callback callable: This can be used to change or extend the default
     *                            settings of the list renderer. The callback receives the preconfigured
     *                            instance as parameter right before the list is rendered.
     *
     *
     * @return string
     */
    public function renderDatabaseRecordList(string $table, array $fields, array $options = []): string
    {
        // Prepare the options
        $options = Options::make($options, [
            'limit'    => [
                'type'    => 'int',
                'default' => 20,
            ],
            'pid'      => [
                'type'    => 'int',
                'default' => function () {
                    return $this->TypoContext()->Pid()->getCurrent();
                },
            ],
            'where'    => [
                'type'    => 'string',
                'default' => '',
            ],
            'callback' => [
                'type'    => ['callable', 'null'],
                'default' => null,
            ],
        ]);

        // Prepare object
        $pid      = $options['pid'];
        $pageInfo = BackendUtility::readPageAccess($options['pid'], '');
        /** @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $backendUser */
        $backendUser = $GLOBALS['BE_USER'];

        /** @var DatabaseRecordList $dbList */
        $dbList                            = $this->getWithoutDi(DatabaseRecordList::class);
        $dbList->script                    = GeneralUtility::getIndpEnv('REQUEST_URI');
        $dbList->thumbs                    = $backendUser->uc['thumbnailsByDefault'];
        $dbList->allFields                 = 1;
        $dbList->clickTitleMode            = 'edit';
        $dbList->calcPerms                 = $backendUser->calcPerms($pageInfo);
        $dbList->showClipboard             = 0;
        $dbList->disableSingleTableView    = 1;
        $dbList->pageRow                   = $pageInfo;
        $dbList->displayFields             = false;
        $dbList->dontShowClipControlPanels = true;
        $dbList->counter++;

        $pointer = MathUtility::forceIntegerInRange($this->TypoContext()->Request()->getGet('pointer'), 0);
        $dbList->start($pid, $table, $pointer, '', 0, $options['limit']);
        $dbList->script = $_SERVER['REQUEST_URI'];
        $dbList->setDispFields();

        // Apply the field list filter
        if (! empty($fields)) {
            $dbList->setFields = [$table => $fields];
        }

        // Trigger the callback if we have one
        if (is_callable($options['callback'])) {
            call_user_func($options['callback'], $dbList);
        }

        // Register the event handler for injecting our additional where clause
        $this->dbRecordFilterTmp = [];
        if (! empty($options['where'])) {
            if (! $this->dbRecordFilterRegistered) {
                $this->dbRecordFilterRegistered = true;

                $this->getInstanceOf(TypoEventBus::class)->addListener(
                    DbListQueryFilterEvent::class, function (DbListQueryFilterEvent $event) {
                    // Skip if the event was already emitted
                    if ($this->dbRecordFilterTmp['emitted'] || empty($this->dbRecordFilterTmp['options'])) {
                        return;
                    }
                    $this->dbRecordFilterTmp['emitted'] = true;
                    $options                            = $this->dbRecordFilterTmp['options'];

                    // Inject our where statement
                    $whereParts = explode(' OR ', $event->getAdditionalWhereClause());
                    $event->setAdditionalWhereClause(
                        implode(' ' . $options['where'] . ' OR ', $whereParts) . ' ' . $options['where']
                    );

                    // Move all pseudo fields to the right...
                    $fieldArray         = $event->getListRenderer()->fieldArray;
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

        // Generate the list
        $dbList->generateList();


        // Check for empty response
        if ($dbList->totalItems === 0) {
            return '';
        }

        // Append T3 location
        $result     = $dbList->HTMLcode;
        $requestUrl = GeneralUtility::quoteJSvalue(rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI')));
        /** @noinspection JSUnresolvedVariable */
        $result .= '<script type="text/javascript">if(typeof T3_THIS_LOCATION === \'undefined\') T3_THIS_LOCATION = '
                   . $requestUrl . '; </script>';

        // Done
        return $result;
    }
}
