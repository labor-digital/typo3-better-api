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
 * Last modified: 2021.05.10 at 18:57
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Sql;


use Doctrine\DBAL\Types\TextType;
use LaborDigital\T3ba\Core\Di\NoDiInterface;

class FallbackType extends TextType implements NoDiInterface
{
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return SqlRegistry::FALLBACK_TYPE_NAME;
    }
}
