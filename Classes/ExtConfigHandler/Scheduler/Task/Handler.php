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


namespace LaborDigital\T3ba\ExtConfigHandler\Scheduler\Task;


use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigHandler;
use LaborDigital\T3ba\ExtConfig\ExtConfigException;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class Handler extends AbstractExtConfigHandler
{
    /**
     * The list of all gathered task definitions
     *
     * @var array[]
     */
    protected $tasks = [];
    
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        // Without the scheduler extension we do nothing!
        if (! ExtensionManagementUtility::isLoaded('scheduler')) {
            return;
        }
        
        $configurator->registerLocation('Classes/Scheduler');
        $configurator->registerInterface(ConfigureTaskInterface::class);
        $configurator->setAllowOverride(false);
    }
    
    /**
     * @inheritDoc
     * @throws \LaborDigital\T3ba\ExtConfig\ExtConfigException
     */
    public function handle(string $class): void
    {
        if (! in_array(AbstractTask::class, class_parents($class), true)) {
            throw new ExtConfigException(
                'Could not configure the scheduler task for class: ' . $class
                . ' because it does not extend the ' . AbstractTask::class . ' class!');
        }
        
        /** @var \LaborDigital\T3ba\ExtConfigHandler\Scheduler\Task\TaskConfigurator $configurator */
        $configurator = $this->getInstanceWithoutDi(
            TaskConfigurator::class, [
                Inflector::toHuman($this->context->getExtKey()) . ': '
                . Inflector::toHuman(Path::classBasename($class)),
                $class,
            ]
        );
        
        call_user_func([$class, 'configure'], $configurator, $this->context);
        
        $this->context->getState()->mergeIntoArray(
            'typo.globals.TYPO3_CONF_VARS.SC_OPTIONS.scheduler.tasks',
            [
                $class => $this->context->replaceMarkers(
                    array_merge(
                        $configurator->getOptions(),
                        [
                            'extension' => '{{extKey}}',
                            'title' => $configurator->getTitle(),
                            'description' => $configurator->getDescription(),
                        ]
                    )
                ),
            ]
        );
    }
    
    /**
     * @inheritDoc
     */
    public function prepare(): void { }
    
    /**
     * @inheritDoc
     */
    public function finish(): void { }
    
    
}
