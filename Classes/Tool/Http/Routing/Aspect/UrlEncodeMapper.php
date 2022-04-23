<?php
/*
 * Copyright 2022 LABOR.digital
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
 * Last modified: 2022.04.22 at 22:06
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Http\Routing\Aspect;


use TYPO3\CMS\Core\Routing\Aspect\MappableAspectInterface;

class UrlEncodeMapper implements MappableAspectInterface
{
    /**
     * @inheritDoc
     */
    public function generate(string $value): ?string
    {
        return urlencode($value);
    }
    
    /**
     * @inheritDoc
     */
    public function resolve(string $value): ?string
    {
        return urldecode($value);
    }
    
}