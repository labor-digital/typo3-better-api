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
 * Last modified: 2021.06.27 at 16:27
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
 * Last modified: 2020.03.19 at 18:43
 */

namespace LaborDigital\T3ba\FormEngine\Addon;

use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Event\FormEngine\BackendFormNodeFilterEvent;
use LaborDigital\T3ba\FormEngine\Util\FormEngineChangeFunctionBuilder;
use TYPO3\CMS\Backend\Form\Element\GroupElement;

class GroupElementsCanTriggerReload implements NoDiInterface
{
    /**
     * Ensures that the on-change dialog is only called once and does not open multiple modals
     *
     * @param   \LaborDigital\T3ba\Event\FormEngine\BackendFormNodeFilterEvent  $event
     */
    public static function onNodeFilter(BackendFormNodeFilterEvent $event): void
    {
        if (! $event->getNode() instanceof GroupElement) {
            return;
        }
        
        $data = $event->getProxy()->getData();
        $changeFunc = $data['parameterArray']['fieldChangeFunc']['alert'] ?? null;
        
        if (! is_string($changeFunc)) {
            return;
        }
        
        $hash = md5(microtime(true) . random_bytes(128));
        $storageVar = 'window._GROUP_ON_CHANGE_' . $hash;
        $changeFunc = 'clearTimeout(' . $storageVar . ');' . $storageVar . '=setTimeout(function(){' .
                      $changeFunc .
                      ';},150);';
        
        $data['parameterArray']['fieldChangeFunc']['alert'] = $changeFunc;
        $event->getProxy()->setData($data);
    }
}
