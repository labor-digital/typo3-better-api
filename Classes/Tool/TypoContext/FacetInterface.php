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
 * Last modified: 2021.05.02 at 19:11
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
 * Last modified: 2020.05.12 at 12:46
 */

namespace LaborDigital\T3ba\Tool\TypoContext;

use LaborDigital\T3ba\Core\Di\PublicServiceInterface;

/**
 * Class FacetInterface
 *
 * A facet is basically an aspect without the strange "get()" method
 *
 * @package LaborDigital\T3ba\Tool\TypoContext
 */
interface FacetInterface extends PublicServiceInterface
{
    /**
     * MUST return a unique, not empty identifier that will be used as method name on the context object
     * Example: An identifier: "foo" will be selectable at $typoContext->foo()
     *
     * @return string
     */
    public static function getIdentifier(): string;
}