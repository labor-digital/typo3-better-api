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
 * Last modified: 2020.03.20 at 11:39
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter;

use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\Event\Events\SqlDefinitionFilterEvent;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class SqlDefinitionFilterEventAdapter extends AbstractCoreHookEventAdapter
{
    
    /**
     * @inheritDoc
     */
    public static function bind(): void
    {
        TypoContainer::getInstance()->get(Dispatcher::class)->connect(
            'TYPO3\\CMS\\Install\\Service\\SqlExpectedSchemaService',
            'tablesDefinitionIsBeingBuilt',
            function ($definitions) {
                static::$bus->dispatch(($e = new SqlDefinitionFilterEvent($definitions)));
                return [$e->getDefinitions()];
            }
        );
    }
}
