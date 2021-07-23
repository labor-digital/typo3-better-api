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
 * Last modified: 2021.07.19 at 14:28
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\UpgradeWizard;


use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigHandler;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;
use ReflectionClass;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class Handler extends AbstractExtConfigHandler
{
    protected $wizards = [];
    
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $configurator->registerInterface(UpgradeWizardInterface::class)
                     ->registerLocation('Classes/Upgrade')
                     ->registerLocation('Classes/UpgradeWizard');
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
        try {
            $reflection = new ReflectionClass($class);
            $instance = $reflection->newInstanceWithoutConstructor();
            if (is_callable([$instance, 'getIdentifier'])) {
                $identifier = $instance->getIdentifier();
            }
        } catch (\Throwable $e) {
        }
        
        if (! $identifier) {
            $identifier = Inflector::toCamelBack($this->context->getExtKey()) .
                          '_' . Inflector::toCamelBack(Path::classBasename($class));
        }
        
        $this->wizards[$identifier] = $class;
    }
    
    /**
     * @inheritDoc
     */
    public function finish(): void
    {
        $this->context->getState()->mergeIntoArray(
            'typo.globals.TYPO3_CONF_VARS.SC_OPTIONS.ext/install.update',
            $this->wizards
        );
    }
    
}