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

namespace LaborDigital\T3ba\Event\FormEngine;

use LaborDigital\T3ba\Event\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\T3ba\Event\FormEngine\Adapter\FormFilterEventAdapter;

/**
 * Class BackendFormFilterEvent
 *
 * This filter can be used to filter the tca as well as the as the raw table data when the backend builds a form
 * using the form engine. The event contains all the data that are passed to objects that implement the
 * FormDataProviderInterface interface.
 *
 * The events will be called between the DatabaseRowInitializeNew and TcaGroup data providers to make sure you have
 * all data you may need for the form, but before the later stages start manipulating the TCA.
 */
class FormFilterEvent implements CoreHookEventInterface
{
    use FormFilterEventTrait;
    
    /**
     * @inheritDoc
     */
    public static function getAdapterClass(): string
    {
        return FormFilterEventAdapter::class;
    }
}
