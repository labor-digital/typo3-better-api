<?php
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
 * Last modified: 2021.02.23 at 11:31
 */
/** @var $_EXTKEY string */
$EM_CONF[$_EXTKEY] = [
    'title' => 'T3BA',
    'description' => 'A package that overhauls the way you work with TYPO3 providing you with a new, better core API',
    'author' => 'Martin Neundorfer',
    'author_email' => 'm.neundorfer@labor.digital',
    'category' => 'misc',
    'author_company' => 'LABOR.digital',
    'shy' => '',
    'conflicts' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 1,
    'lockType' => '',
    'version' => "10.42.0",
    'constraints' => [
        'depends' => [
            'typo3' => '10.0.0-10.99.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'suggests' => [
    ],
];
