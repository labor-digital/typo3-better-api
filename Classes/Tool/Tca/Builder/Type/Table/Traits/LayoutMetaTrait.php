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
 * Last modified: 2021.04.29 at 22:17
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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Traits;

trait LayoutMetaTrait
{
    
    /**
     * Stores additional information, that were parsed out of the showitem string
     *
     * @var array
     */
    protected $layoutMeta = [];
    
    /**
     * Returns additional information, that were parsed out of the showitem string
     *
     * @return array
     */
    public function getLayoutMeta(): array
    {
        return $this->layoutMeta;
    }
    
    /**
     * Sets additional information, that were parsed out of the showitem string
     *
     * @param   array  $meta
     *
     * @return $this
     */
    public function setLayoutMeta(array $meta)
    {
        $this->layoutMeta = $meta;
        
        return $this;
    }
}
