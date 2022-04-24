<?php
/*
 * Copyright 2022 LABOR.digital
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
 * Last modified: 2022.04.23 at 12:15
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Event\Frontend\Adapter;


use LaborDigital\T3ba\Event\CoreHookAdapter\AbstractCoreHookEventAdapter;
use LaborDigital\T3ba\Event\Frontend\HashBaseArgFilterEvent;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class HashBaseArgFilterEventAdapter extends AbstractCoreHookEventAdapter
{
    /**
     * @inheritDoc
     */
    public static function bind(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['createHashBase'][static::class]
            = static::class . '->apply';
    }
    
    public function apply(array &$args, TypoScriptFrontendController $tsfe): void
    {
        $args['hashParameters'] = static::$bus->dispatch(
            new HashBaseArgFilterEvent($args['hashParameters'], $args['createLockHashBase'], $tsfe)
        )->getArgs();
    }
}