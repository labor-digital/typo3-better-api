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
 * Last modified: 2020.10.05 at 16:14
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Page;

use LaborDigital\T3BA\Core\Di\StaticContainerAwareTrait;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Utility\RootlineUtility;

class ExtendedRootLineUtility extends RootlineUtility
{
    use StaticContainerAwareTrait;

    /**
     * Executes the root line request with additional options applied
     *
     * @param   int    $pageId        The page id to generate the root line for
     * @param   array  $options       Additional options for the root line renderer
     *                                - includeAllNotDeleted bool (FALSE): If set to true this will generate the
     *                                rootline without caring for permissions
     *                                - additionalFields array: A list of additional fields to fetch for the
     *                                generated root line
     *                                - mountPoint string: An optional mount point parameter to use while
     *                                generating the root line
     *
     * @return array
     */
    public static function getWith(int $pageId, array $options = []): array
    {
        $options = Options::make($options, [
            'includeAllNotDeleted' => [
                'type'    => 'bool',
                'default' => false,
            ],
            'additionalFields'     => [
                'type'    => 'array',
                'default' => [],
            ],
            'mountPoint'           => [
                'type'    => 'string',
                'default' => '',
            ],
        ]);

        $instance = static::makeInstance(
            RootlineUtility::class,
            [
                $pageId,
                $options['mountPoint'],
                static::getService(TypoContext::class)->getRootContext(),
            ]
        );

        $instance->cacheIdentifier
            .= '_' . ((int)$options['includeAllNotDeleted']) . '_' . implode(',', $options['additionalFields'])
               . '_extended';
        if (isset(parent::$localCache[$instance->cacheIdentifier])) {
            return parent::$localCache[$instance->cacheIdentifier];
        }

        $backupPermission = $instance->context->where_groupAccess;
        if ($options['includeAllNotDeleted']) {
            $instance->context->where_groupAccess = '';
        }

        $backupFields = parent::$rootlineFields;
        try {
            parent::$rootlineFields = array_merge(parent::$rootlineFields, $options['additionalFields']);

            return parent::$localCache[$instance->cacheIdentifier] = $instance->get();
        } finally {
            parent::$rootlineFields               = $backupFields;
            $instance->context->where_groupAccess = $backupPermission;
        }
    }
}
