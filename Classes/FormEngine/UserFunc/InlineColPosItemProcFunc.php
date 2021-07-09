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
 * Last modified: 2021.07.09 at 09:45
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\FormEngine\UserFunc;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\NoDiInterface;

class InlineColPosItemProcFunc implements NoDiInterface
{
    use ContainerAwareTrait;
    
    public function itemsProcFunc(array &$params): void
    {
        $params['items'] = [
            [
                $this->cs()->translator->translateBe('t3ba.t.tt_content.colPos.inlineContent'),
                '-88',
                null,
            ],
        ];
    }
}