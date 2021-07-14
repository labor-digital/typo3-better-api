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
 * Last modified: 2021.07.14 at 18:24
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\EventHandler;


use LaborDigital\T3ba\Event\Core\ExtLocalConfLoadedEvent;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Extbase\Event\Persistence\ModifyQueryBeforeFetchingObjectDataEvent;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Selector;

class TablePreview implements LazyEventSubscriberInterface
{
    /**
     * @var \LaborDigital\T3ba\Tool\TypoContext\Aspect\BetterVisibilityAspect
     */
    protected $visibilityAspect;
    
    public function __construct(TypoContext $typoContext)
    {
        $this->visibilityAspect = $typoContext->visibility();
    }
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(ModifyQueryBeforeFetchingObjectDataEvent::class, 'onExtbaseQueryFilter');
        $subscription->subscribe(ExtLocalConfLoadedEvent::class, 'onExtLocalConf', ['priority' => -200]);
    }
    
    /**
     * Executed every time extbase queries data from the database.
     * It validates if the table actually matches one of the allowed tables, and automatically disables
     * the hidden constraint for those included.
     *
     * @param   \TYPO3\CMS\Extbase\Event\Persistence\ModifyQueryBeforeFetchingObjectDataEvent  $e
     */
    public function onExtbaseQueryFilter(ModifyQueryBeforeFetchingObjectDataEvent $e): void
    {
        $query = $e->getQuery();
        $source = $query->getSource();
        if (! $source instanceof Selector) {
            return;
        }
        
        if (! $this->visibilityAspect->includeHiddenOfTable($source->getSelectorName())) {
            return;
        }
        
        $settings = $query->getQuerySettings();
        $settings->setIgnoreEnableFields(true);
        $settings->setEnableFieldsToBeIgnored(['disabled']);
    }
    
    /**
     * Registers the filter tables hook for the extended hidden restriction.
     * The hook validates if a table actually matches one of the allowed tables and automatically
     * disables the constraint if so.
     */
    public function onExtLocalConf(): void
    {
        HiddenRestriction::$hooks[static::class] = [$this, 'filterTables'];
    }
    
    /**
     * Hook function to filter the tables of the extended hidden restriction
     *
     * @param   array  $tables
     */
    public function filterTables(array &$tables): void
    {
        $tables = array_filter($tables, function (string $tableName) {
            return ! $this->visibilityAspect->includeHiddenOfTable($tableName);
        });
    }
}