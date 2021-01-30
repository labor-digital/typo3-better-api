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
 * Last modified: 2021.01.30 at 12:35
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\FieldPreset;


use LaborDigital\T3BA\ExtConfig\AbstractExtConfigHandler;
use LaborDigital\T3BA\Tool\Tca\Builder\FieldPreset\FieldPresetInterface;
use Neunerlei\Configuration\Handler\HandlerConfigurator;

class FieldPresetHandler extends AbstractExtConfigHandler
{

    /**
     * @var \LaborDigital\T3BA\ExtConfigHandler\FieldPreset\FieldPresetListGenerator
     */
    protected $listGenerator;

    /**
     * FieldPresetHandler constructor.
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\FieldPreset\FieldPresetListGenerator  $listGenerator
     */
    public function __construct(FieldPresetListGenerator $listGenerator)
    {
        $this->listGenerator = $listGenerator;
    }

    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $configurator->registerLocation('Classes/FormEngine/FieldPreset');
        $configurator->registerInterface(FieldPresetInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function prepare(): void { }

    /**
     * @inheritDoc
     */
    public function handle(string $class): void
    {
        $this->listGenerator->registerClass($class, $this->definition->isOverride($class));
    }

    /**
     * @inheritDoc
     */
    public function finish(): void
    {
        // Store the presets
        $presets = $this->listGenerator->getPresets();
        $this->context->getState()->set('tca.fieldPresets', $presets);

        // Generate the autocomplete helper in dev mode
        if ($this->context->env()->isDev()) {
            $this->makeAutocompleteHelper($presets);
        }
    }

    /**
     * Loads and triggers the autocomplete helper generator for development environments
     *
     * @param   array  $presets
     */
    protected function makeAutocompleteHelper(array $presets): void
    {
        /** @var \LaborDigital\T3BA\ExtConfigHandler\FieldPreset\FieldPresetAutocompleteGenerator $generator */
        $generator = $this->getInstanceWithoutDi(
            FieldPresetAutocompleteGenerator::class,
            [$this->context]
        );

        $generator->generate($presets);
    }
}
