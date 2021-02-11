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
 * Last modified: 2020.10.18 at 20:19
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Tool\DataHook\Definition;

use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Tool\DataHook\DataHookException;
use LaborDigital\T3BA\Tool\DataHook\Definition\Traverser\TcaTraverser;
use LaborDigital\T3BA\Tool\DataHook\FieldPacker\FieldPackerInterface;
use LaborDigital\T3BA\Tool\Tca\TcaUtil;
use LaborDigital\T3BA\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\Arrays\Arrays;

class DefinitionResolver
{
    use TypoContextAwareTrait;
    use ContainerAwareTrait;

    /**
     * @var \LaborDigital\T3BA\Tool\DataHook\Definition\Traverser\TcaTraverser
     */
    protected $traverser;

    /**
     * DefinitionResolver constructor.
     *
     * @param   \LaborDigital\T3BA\Tool\DataHook\Definition\Traverser\TcaTraverser  $traverser
     */
    public function __construct(TcaTraverser $traverser)
    {
        $this->traverser = $traverser;
    }

    /**
     * Resolves the hook definition, including it's handler definitions based on the given table name and data
     * by traversing the TCA and extracting the included configuration.
     *
     * @param   string  $type       One of DataHookTypes::TYPE_ constant values
     * @param   string  $tableName  The name of the table to traverse the tca for
     * @param   array   $data       The given data to match against the tca of the table
     *
     * @return \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition
     */
    public function resolve(string $type, string $tableName, array $data): DataHookDefinition
    {
        $definition = $this->resolveBasicDefinitionObject($type, $tableName, $data);
        $this->applyFieldPackers($definition);
        $this->resolveHandlerDefinitions($definition);

        return $definition;
    }

    /**
     * Builds the basic definition object and loads the tca array
     *
     * @param   string  $type
     * @param   string  $tableName
     * @param   array   $data
     *
     * @return \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition
     * @throws \LaborDigital\T3BA\Tool\DataHook\DataHookException '
     */
    protected function resolveBasicDefinitionObject(string $type, string $tableName, array $data): DataHookDefinition
    {
        $definition            = $this->makeInstance(DataHookDefinition::class);
        $definition->type      = $type;
        $definition->data      = $data;
        $definition->dataRaw   = $data;
        $definition->tableName = $tableName;

        if (! Arrays::hasPath($GLOBALS, ['TCA', $tableName])) {
            throw new DataHookException('Failed to execute data hook on: ' . $tableName
                                        . ' because the table is not defined in the TCA!');
        }

        TcaUtil::runWithResolvedTypeTca($data, $tableName, static function (array $typeTca) use ($definition) {
            $definition->tca = $typeTca;
        });

        return $definition;
    }

    /**
     * Resolves the field packer classes and instantiates them on the definition object.
     * It also executes the unpacking of the given data
     *
     * @param   \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition  $definition
     *
     * @throws \LaborDigital\T3BA\Tool\DataHook\DataHookException
     */
    protected function applyFieldPackers(DataHookDefinition $definition): void
    {
        $typoContext = $this->getTypoContext();
        foreach ($typoContext->config()->getConfigValue('t3ba.dataHook.fieldPackers', []) as $fieldPackerClass) {
            if (! class_exists($fieldPackerClass)
                || ! in_array(FieldPackerInterface::class, class_implements($fieldPackerClass), true)) {
                throw new DataHookException('Invalid field packer class given: ' . $fieldPackerClass);
            }
            /** @var FieldPackerInterface $packer */
            $packer                       = $this->getService($fieldPackerClass);
            $definition->fieldPackers[]   = $packer;
            $definition->unpackedFields[] = $packer->unpackFields($definition);
        }
    }

    /**
     * Resolves the definition, containing the handler instances for the current data hook
     *
     * @param   \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition  $definition
     */
    protected function resolveHandlerDefinitions(DataHookDefinition $definition): void
    {
        $this->traverser->initialize($definition)->traverse();
    }
}
