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
 * Last modified: 2020.08.25 at 19:45
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event\CoreHookAdapter;


use LaborDigital\T3BA\Event\TypoScriptConfigArrayPostProcEvent;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class TypoScriptConfigArrayPostProcEventAdapter extends AbstractCoreHookEventAdapter
{

    /**
     * @inheritDoc
     */
    public static function bind(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc'][static::class]
            = static::class . '->handle';
    }

    public function handle(array $params, TypoScriptFrontendController $tsfe): void
    {
        static::$bus->dispatch($e = new TypoScriptConfigArrayPostProcEvent($params['config'], $tsfe));
        $params['config'] = $e->getConfig();
    }
}
