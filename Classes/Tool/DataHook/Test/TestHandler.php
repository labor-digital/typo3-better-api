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
 * Last modified: 2020.10.19 at 08:41
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\DataHook\Test;


use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;
use LaborDigital\T3BA\Tool\DataHook\DataHookContext;

class TestHandler implements PublicServiceInterface
{

    public function global(DataHookContext $context)
    {
        dbge($context->getType(), $context);

        return;
        dbge($context->getUid(), $context->getData(), $context->getTableName());
    }

    public function field(DataHookContext $context)
    {
        $context->setData('D' . microtime());
//        dbge($context->getType(), $context->getTableName(), $context->getData(), $context->getRow());
    }
}
