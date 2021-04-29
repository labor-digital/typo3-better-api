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


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table;


class TableDefaults
{
    public const FIELD_TCA
        = [
            'exclude' => 1,
        ];
    
    public const TYPE_TCA
        = [
            'showitem' => '
                --div--;t3ba.tab.general,
                --div--;t3ba.tab.access,
                --palette--;;hidden,
                --palette--;;access,
                --div--;t3ba.tab.language,
                --palette--;;language',
        ];
    
    public const TABLE_TCA
        = [
            'ctrl' => [
                'label' => 'uid',
                'hideAtCopy' => true,
                'tstamp' => 'tstamp',
                'crdate' => 'crdate',
                'cruser_id' => 'cruser_id',
                'versioningWS' => true,
                'origUid' => 't3_origuid',
                'editlock' => 'editlock',
                'prepentAtCopy' => '',
                'transOrigPointerField' => 'l10n_parent',
                'translationSource' => 'l10n_source',
                'transOrigDiffSourceField' => 'l10n_diffsource',
                'languageField' => 'sys_language_uid',
                'enablecolumns' => [
                    'disabled' => 'hidden',
                    'starttime' => 'starttime',
                    'endtime' => 'endtime',
                    'fe_group' => 'fe_group',
                ],
                'delete' => 'deleted',
            ],
            
            'columns' => [
                'sys_language_uid' => [
                    'exclude' => true,
                    'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:sys_language_uid_formlabel',
                    'config' => [
                        'type' => 'select',
                        'renderType' => 'selectSingle',
                        'foreign_table' => 'sys_language',
                        'foreign_table_where' => 'ORDER BY sys_language.title',
                        'items' => [
                            ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages', -1],
                            ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.default_value', 0],
                        ],
                        'default' => 0,
                        'fieldWizard' => [
                            'selectIcons' => [
                                'disabled' => false,
                            ],
                        ],
                    ],
                ],
                'l10n_parent' => [
                    'displayCond' => 'FIELD:sys_language_uid:>:0',
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
                    'config' => [
                        'type' => 'select',
                        'renderType' => 'selectSingle',
                        'items' => [['', 0]],
                        'foreign_table' => '{{table}}',
                        'foreign_table_where' => 'AND {{table}}.uid=###REC_FIELD_l10n_parent### AND {{table}}.sys_language_uid IN (-1,0)',
                        'default' => 0,
                    ],
                ],
                'l10n_diffsource' => [
                    'config' => [
                        'type' => 'passthrough',
                        'default' => '',
                    ],
                ],
                'l10n_source' => [
                    'config' => [
                        'type' => 'passthrough',
                    ],
                ],
                'hidden' => [
                    'exclude' => true,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
                    'config' => [
                        'type' => 'check',
                        'renderType' => 'checkboxToggle',
                        'items' => [
                            [
                                0 => '',
                                1 => '',
                                'invertStateDisplay' => true,
                            ],
                        ],
                    ],
                ],
                'cruser_id' => [
                    'label' => 'cruser_id',
                    'config' => ['type' => 'passthrough'],
                ],
                'pid' => [
                    'label' => 'pid',
                    'config' => ['type' => 'passthrough'],
                ],
                'crdate' => [
                    'label' => 'crdate',
                    'config' => ['type' => 'passthrough'],
                ],
                'tstamp' => [
                    'label' => 'tstamp',
                    'config' => ['type' => 'passthrough'],
                ],
                'sorting' => [
                    'label' => 'sorting',
                    'config' => ['type' => 'passthrough'],
                ],
                'starttime' => [
                    'exclude' => true,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
                    'config' => [
                        'type' => 'input',
                        'renderType' => 'inputDateTime',
                        'eval' => 'datetime,int',
                        'default' => 0,
                        'behaviour' => [
                            'allowLanguageSynchronization' => true,
                        ],
                    ],
                ],
                'endtime' => [
                    'exclude' => true,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
                    'config' => [
                        'type' => 'input',
                        'renderType' => 'inputDateTime',
                        'eval' => 'datetime,int',
                        'default' => 0,
                        'range' => [
                            'upper' => 2208988800,
                        ],
                        'behaviour' => [
                            'allowLanguageSynchronization' => true,
                        ],
                    ],
                ],
                'fe_group' => [
                    'exclude' => true,
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.fe_group',
                    'config' => [
                        'type' => 'select',
                        'renderType' => 'selectMultipleSideBySide',
                        'size' => 5,
                        'maxitems' => 20,
                        'items' =>
                            [
                                ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login', -1],
                                ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login', -2],
                                [
                                    'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
                                    '--div--',
                                ],
                            ],
                        'exclusiveKeys' => '-1,-2',
                        'foreign_table' => 'fe_groups',
                        'foreign_table_where' => 'ORDER BY fe_groups.title',
                    ],
                ],
                't3_origuid' => [
                    'config' => [
                        'default' => 0,
                        'type' => 'passthrough',
                    ],
                ],
                't3ver_label' => [
                    'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
                    'config' => [
                        'max' => 255,
                        'size' => 30,
                        'type' => 'input',
                    ],
                ],
            ],
            'types' => [
                '0' => self::TYPE_TCA,
            ],
            'palettes' => [
                'hidden' => [
                    'showitem' => 'hidden',
                ],
                'language' => [
                    'showitem' => 'sys_language_uid,l10n_parent',
                ],
                'access' => [
                    'showitem' => 'starttime,endtime,--linebreak--,fe_group',
                ],
            ],
        ];
    
    public const CONTENT_TYPE_TCA
        = [
            'showitem' => '
                    --div--;t3ba.tab.general,
					--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
					--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
					layout;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:layout_formlabel,
                    --div--;t3ba.tab.language,
					--palette--;;language,
                    --div--;t3ba.tab.access,
					--palette--;;hidden,
					--palette--;;access,
					--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended,',
        ];
}
