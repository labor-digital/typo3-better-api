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
 * Last modified: 2020.08.23 at 23:11
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\DependencyInjection;

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

        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        return $this->instances[$id] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function has($id)
    {
        return isset($this->instances[$id]);
    }

}
