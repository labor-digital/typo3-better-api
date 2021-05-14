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
 * Last modified: 2021.05.10 at 17:49
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Command;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigHandler;
use LaborDigital\T3ba\ExtConfig\ExtConfigException;
use LaborDigital\T3ba\ExtConfig\Interfaces\DiBuildTimeHandlerInterface;
use LaborDigital\T3ba\ExtConfigHandler\Di\BuildTimeHandler;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class Handler extends AbstractExtConfigHandler implements DiBuildTimeHandlerInterface, NoDiInterface
{
    protected $commands = [];
    
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $configurator->registerLocation('Classes/Command');
        $configurator->registerInterface(ConfigureCliCommandInterface::class);
        $configurator->setAllowOverride(false);
        $configurator->executeThisHandlerAfter(BuildTimeHandler::class);
    }
    
    /**
     * @inheritDoc
     */
    public function prepare(): void
    {
    }
    
    /**
     * @inheritDoc
     * @throws \LaborDigital\T3ba\ExtConfig\ExtConfigException
     */
    public function handle(string $class): void
    {
        if (! in_array(Command::class, class_parents($class), true)) {
            throw new ExtConfigException(
                'Invalid command configuration class: ' . $class
                . ' a command has to extend the symfony command class: ' . Command::class);
        }
        
        /** @var \Symfony\Component\Console\Command\Command $c */
        $c = new $class();
        
        $name = $c->getName();
        if ($name === null) {
            $name = Inflector::toCamelBack($this->context->getExtKey()) . ':'
                    . Inflector::toCamelBack(Path::classBasename($class));
            if (str_ends_with($name, 'Command')) {
                $name = substr($name, 0, -7);
            }
        }
        
        $this->commands[$name] = $class;
    }
    
    /**
     * @inheritDoc
     */
    public function finish(): void
    {
        if (empty($this->commands)) {
            return;
        }
        
        /** @var ContainerBuilder $containerBuilder */
        $containerBuilder = $this->context->getContainer()->get(ContainerBuilder::class);
        foreach ($this->commands as $name => $class) {
            try {
                $containerBuilder->findDefinition($class)->addTag('console.command', ['command' => $name]);
            } catch (ServiceNotFoundException $exception) {
            }
        }
    }
    
}
