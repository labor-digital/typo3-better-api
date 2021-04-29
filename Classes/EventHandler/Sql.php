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
 * Last modified: 2021.02.08 at 14:14
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\EventHandler;


use LaborDigital\T3BA\Core\VarFs\VarFs;
use LaborDigital\T3BA\Event\Core\TcaCompletelyLoadedEvent;
use LaborDigital\T3BA\Tool\Sql\SqlRegistry;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

class Sql implements LazyEventSubscriberInterface
{
    public const STORAGE_KEY = 'registry.sql';
    
    /**
     * Allows the outside world to disable the sql injection.
     *
     * @var bool
     */
    public static $enabled = true;
    
    /**
     * The dynamic sql storage registry
     *
     * @var \LaborDigital\T3BA\Tool\Sql\SqlRegistry
     */
    protected $registry;
    
    /**
     * The file system storage for sql related data
     *
     * @var VarFs
     */
    protected $fsMount;
    
    /**
     * SqlEventHandler constructor.
     *
     * @param   \LaborDigital\T3BA\Tool\Sql\SqlRegistry  $registry
     * @param   \LaborDigital\T3BA\Core\VarFs\VarFs      $fs
     */
    public function __construct(SqlRegistry $registry, VarFs $fs)
    {
        $this->registry = $registry;
        $this->fsMount = $fs->getMount('Sql');
    }
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(TcaCompletelyLoadedEvent::class, 'onTcaLoaded', ['priority' => -5000]);
        $subscription->subscribe(AlterTableDefinitionStatementsEvent::class, 'onSqlTableDefinitions');
    }
    
    public function onTcaLoaded(): void
    {
        $this->fsMount->setFileContent(static::STORAGE_KEY, $this->registry->dump());
    }
    
    public function onSqlTableDefinitions(AlterTableDefinitionStatementsEvent $e): void
    {
        if (! static::$enabled || ! $this->fsMount->hasFile(static::STORAGE_KEY)) {
            return;
        }
        
        $e->addSqlData($this->fsMount->getFileContent(static::STORAGE_KEY));
    }
}
