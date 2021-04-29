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


use LaborDigital\T3BA\Event\ExtConfig\FieldPresetFilterEvent;
use LaborDigital\T3BA\ExtConfig\Abstracts\AbstractExtConfigHandler;
use LaborDigital\T3BA\Tool\Tca\Builder\FieldPreset\FieldPresetInterface;
use Neunerlei\Configuration\Handler\HandlerConfigurator;

class Handler extends AbstractExtConfigHandler
{
    
    /**
     * @var \LaborDigital\T3BA\ExtConfigHandler\FieldPreset\ListGenerator
     */
    protected $listGenerator;
    
    /**
     * FieldPresetHandler constructor.
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\FieldPreset\ListGenerator  $listGenerator
     */
    public function __construct(ListGenerator $listGenerator)
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
        
        $presets = $this->context->getTypoContext()->di()->cs()->eventBus
            ->dispatch(new FieldPresetFilterEvent($presets, $this->context))
            ->getPresets();
        
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
        /** @var \LaborDigital\T3BA\ExtConfigHandler\FieldPreset\AutocompleteGenerator $generator */
        $generator = $this->getInstanceWithoutDi(
            AutocompleteGenerator::class,
            [$this->context->getExtConfigService()->getFsMount()]
        );
        
        $generator->generate($presets);
    }
}
