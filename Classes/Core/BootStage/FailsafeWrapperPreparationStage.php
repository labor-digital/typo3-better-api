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
 * Last modified: 2020.08.23 at 12:42
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\BootStage;


use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\Kernel;
use LaborDigital\T3BA\Core\Util\FailsafeWrapper;
use LaborDigital\T3BA\Event\BootstrapFailsafeDefinitionEvent;

class FailsafeWrapperPreparationStage implements BootStageInterface
{
    /**
     * @inheritDoc
     */
    public function prepare(TypoEventBus $eventBus, Kernel $kernel): void
    {
        $eventBus->addListener(BootstrapFailsafeDefinitionEvent::class,
            static function (BootstrapFailsafeDefinitionEvent $event) {
                FailsafeWrapper::$isFailsafe = $event->isFailsafe();
            });
    }
    
}
