<?php
/*
 * Copyright 2020 Martin Neundorfer (Neunerlei)
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
 * Last modified: 2020.08.09 at 14:49
 */

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
 * Last modified: 2020.03.19 at 13:50
 */

namespace LaborDigital\T3BA\Core\Override;


use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Event\FormEngine\BackendFormNodeDataFilterEvent;
use LaborDigital\T3BA\Tool\FormEngine\FormNodeEventProxy;
use TYPO3\CMS\Backend\Form\BetterApiClassOverrideCopy__NodeFactory;

class ExtendedNodeFactory extends BetterApiClassOverrideCopy__NodeFactory
{

    /**
     * @inheritDoc
     */
    public function create(array $data)
    {
        $eventBus = TypoEventBus::getInstance();

        $data = $eventBus->dispatch(new BackendFormNodeDataFilterEvent($data))->getData();

        return FormNodeEventProxy::makeInstance($eventBus, $this, parent::create($data));
    }
}
