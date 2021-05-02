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


namespace LaborDigital\T3ba\Core\Di;

use Psr\Container\ContainerInterface;

class MiniContainer implements ContainerInterface
{
    /**
     * The list of instances by their service id
     *
     * @var array
     */
    protected $instances = [];
    
    /**
     * MiniContainer constructor.
     *
     * @param   array  $instances  Optional instances to be loaded from the start
     */
    public function __construct(array $instances = [])
    {
        $this->instances = $instances;
    }
    
    /**
     * Sets a specific instance for an id
     *
     * @param   string  $id
     * @param   object  $instance
     */
    public function set(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
    }
    
    /**
     * @inheritDoc
     */
    public function get($id)
    {
        if (! $this->has($id)) {
            return null;
        }
        
        return $this->instances[$id] ?? $this->instances[$id] ?? null;
    }
    
    /**
     * @inheritDoc
     */
    public function has($id): bool
    {
        return isset($this->instances[$id]);
    }
    
}
