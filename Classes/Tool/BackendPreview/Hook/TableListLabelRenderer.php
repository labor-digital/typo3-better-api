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
 * Last modified: 2021.11.08 at 18:34
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\BackendPreview\Hook;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Event\BackendPreview\TableListLabelRenderingEvent;

class TableListLabelRenderer implements NoDiInterface
{
    use ContainerAwareTrait;
    
    public function render(array &$params): void
    {
        if (! is_string($params['options']['t3baClass'] ?? null)) {
            return;
        }
        
        $e = $this->cs()->eventBus->dispatch(new TableListLabelRenderingEvent(
            $params['table'], $params['row'], $params['title'],
            $params['options']['t3ba'] ?? [], $params['options']['t3baClass']
        ));
        
        $params['title'] = $e->getTitle();
    }
    
    public function renderInline(array &$params): void
    {
        $this->render($params);
    }
}