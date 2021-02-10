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
 * Last modified: 2020.10.18 at 21:53
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\DataHook\Definition\Traverser;


use LaborDigital\T3BA\Core\Exception\NotImplementedException;
use LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition;
use Neunerlei\Arrays\Arrays;

class FlexFormTraverser extends AbstractTraverser
{
    /**
     * The root path to the actual flex form field
     *
     * @var array
     */
    protected $rootPath;

    /**
     * The relevant data to traverse
     *
     * @var array
     */
    protected $data;

    /**
     * @inheritDoc
     */
    public function initialize(DataHookDefinition $definition, array $rootPath = []): AbstractTraverser
    {
        $this->rootPath = $rootPath;
        $this->data     = Arrays::getPath($definition->data, $rootPath, []);

        return parent::initialize($definition);
    }

    /**
     * Iterates the flex form data structure to find registered data hook handlers to process
     *
     * @throws \LaborDigital\T3BA\Core\Exception\NotImplementedException
     */
    public function traverse(): void
    {
        // Ignore empty data
        if (empty($this->data)) {
            return;
        }

        return;

        dbge($this->rootPath, $this->data, $this->definition);
        // @todo Implement this
        throw new NotImplementedException();
    }
}
