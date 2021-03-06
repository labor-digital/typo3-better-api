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
use LaborDigital\T3ba\Event\FormEngine\BackendFormNodePostProcessorEvent;
use LaborDigital\T3ba\FormEngine\Util\FormEngineChangeFunctionBuilder;
use TYPO3\CMS\Backend\Form\Element\GroupElement;

class GroupElementsCanTriggerReload implements NoDiInterface
{
    
    /**
     * This applier allows group elements to emit the page reload when they have changed.
     *
     * @param   \LaborDigital\T3ba\Event\FormEngine\BackendFormNodePostProcessorEvent  $event
     */
    public static function onPostProcess(BackendFormNodePostProcessorEvent $event): void
    {
        if (! $event->getNode() instanceof GroupElement) {
            return;
        }
        
        $fieldChangeFunc = $event->getProxy()->getData()['parameterArray']['fieldChangeFunc'] ?? null;
        
        if (empty($fieldChangeFunc)) {
            return;
        }
        
        // Build the change function
        $result = $event->getResult();
        $result['html'] = FormEngineChangeFunctionBuilder::buildOnChangeFunction(
            $result['html'],
            $fieldChangeFunc
        );
        
        $event->setResult($result);
    }
}
