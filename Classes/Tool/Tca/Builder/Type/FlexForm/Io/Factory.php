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
 * Last modified: 2021.02.05 at 18:29
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Io;


use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Core\Di\PublicServiceInterface;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Flex;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Io\Traits\FactoryDefinitionResolverTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Io\Traits\FactoryPopulatorTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableFactory;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaField;

class Factory implements PublicServiceInterface
{
    use ContainerAwareTrait;
    use FactoryDefinitionResolverTrait;
    use FactoryPopulatorTrait;

    /**
     * Creates a new, empty flex form data structure representation
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaField|null  $field
     *
     * @return Flex
     */
    public function create(?TcaField $field = null): Flex
    {
        $field = $field ?? $this->makeStandaloneField();

        return $this->makeInstance(
            Flex::class,
            [
                $field,
                $this,
                $field->getRoot()->getContext(),
            ]
        );
    }

    /**
     * Initializes the flex form structure based on the provided definition
     *
     * @param   Flex    $flex
     * @param   string  $definition
     */
    public function initialize(Flex $flex, string $definition): void
    {
        $flex->clear();

        $def = $this->resolveDefinitionToArray($definition, $flex->getContext()->parent());

        $this->populateElements($flex, $def);
    }

    /**
     * If no field was provided we create a dummy table with a field that will never be used anywhere.
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaField
     */
    protected function makeStandaloneField(): TcaField
    {
        $tableFactory = $this->makeInstance(TableFactory::class);
        $table        = $tableFactory->create('flex-form-dummy-table', $this->getService(ExtConfigContext::class));

        return $table->getType()->getField('flex');
    }

}
