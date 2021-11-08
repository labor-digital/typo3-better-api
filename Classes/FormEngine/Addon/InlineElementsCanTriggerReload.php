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
 * Last modified: 2021.11.08 at 18:14
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\FormEngine\Addon;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Event\FormEngine\BackendFormNodePostProcessorEvent;
use TYPO3\CMS\Backend\Form\Container\InlineControlContainer;

/**
 * Allows inline elements to trigger a reload of the form on change
 */
class InlineElementsCanTriggerReload implements NoDiInterface
{
    public static function onPostProcess(BackendFormNodePostProcessorEvent $event): void
    {
        if (! $event->getNode() instanceof InlineControlContainer) {
            return;
        }
        
        $data = $event->getProxy()->getData();
        $changeFunc = $data['parameterArray']['fieldChangeFunc']['alert'] ?? null;
        
        if (! is_string($changeFunc)) {
            return;
        }
        
        $result = $event->getResult();
        
        if (empty($result) || ! is_array($result['requireJsModules'] ?? null)) {
            return;
        }
        
        $targetKey = 'TYPO3/CMS/Backend/FormEngine/Container/InlineControlContainer';
        foreach ($result['requireJsModules'] as $moduleK => $module) {
            if (! is_array($module) || ! isset($module[$targetKey])) {
                continue;
            }
            
            $function = $module[$targetKey];
            $wrapper = '(function(c){' .
                       'setTimeout(function(){' .
                       'var el = c.getFormFieldForElements();' .
                       'if(el === null) return;' .
                       'var t = 0;' .
                       'var observer = new MutationObserver(function(){' .
                       'clearTimeout(t);' .
                       't = setTimeout(function(){' .
                       $changeFunc . ';' .
                       '}, 150);' .
                       '});' .
                       'observer.observe(el,{attributes:true});' .
                       '}, 500);' .
                       '})($1)';
            $function = preg_replace('~(new InlineControlContainer.*?\));~', $wrapper, $function);
            $result['requireJsModules'][$moduleK][$targetKey] = $function;
        }
        
        $event->setResult($result);
    }
}