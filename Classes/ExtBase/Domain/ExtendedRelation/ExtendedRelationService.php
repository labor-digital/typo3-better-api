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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);
/**
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
 * Last modified: 2020.03.20 at 16:24
 */

namespace LaborDigital\T3ba\ExtBase\Domain\ExtendedRelation;

use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Event\ExtBase\Persistence\DataMapperQueryFilterEvent;
use LaborDigital\T3ba\Tool\Database\BetterQuery\BetterQueryTypo3DbQueryParserAdapter;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;

/**
 * Class ExtendedRelationService
 *
 * @package LaborDigital\T3ba\ExtBase\Domain\ExtendedRelation
 */
class ExtendedRelationService implements PublicServiceInterface
{
    /**
     * True if our internal event handler was registered
     *
     * @var bool
     */
    protected $handlerRegistered = false;
    
    /**
     * The query filter we are currently applying
     *
     * @var callable|null
     */
    protected $filter;
    
    /**
     * @var TypoEventBus
     */
    protected $eventBus;
    
    /**
     * The cached enabled state to avoid a lot of repetitive work
     *
     * @var array
     */
    protected $enabledStateCache = [];
    
    /**
     * ExtendedRelationService constructor.
     *
     * @param   \LaborDigital\T3ba\Core\EventBus\TypoEventBus  $eventBus
     */
    public function __construct(TypoEventBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }
    
    /**
     * Runs the given callback with the extended settings applied
     *
     * @param   array     $settings  The settings to apply while executing the given callback
     *                               - hidden (bool|string|array) FALSE:
     *                               * TRUE: Include all hidden children in all entities
     *                               * FALSE: Go back to the default behaviour
     *                               * \Entity\Class\Name: Allow hidden children for all properties of a given entity
     *                               class
     *                               * [\Entity\Class\Name, \Entity\Class\AnotherName]: Allow hidden children for all
     *                               properties of multiple entity classes
     *                               * [\Entity\Class\Name => "property", \Entity\Class\AnotherName => ["property",
     *                               "foo"]: Allow hidden children for either a single property or a list of properties
     *                               - deleted (bool|string|array) FALSE:
     *                               * TRUE: Include all deleted children in all entities
     *                               * FALSE: Go back to the default behaviour
     *                               * \Entity\Class\Name: Allow deleted children for all properties of a given entity
     *                               class
     *                               * [\Entity\Class\Name, \Entity\Class\AnotherName]: Allow deleted children for all
     *                               properties of multiple entity classes
     *                               * [\Entity\Class\Name => "property", \Entity\Class\AnotherName => ["property",
     *                               "foo"]: Allow deleted children for either a single property or a list of properties
     *
     * @param   callable  $function
     *
     * @return mixed
     */
    public function runWithRelationSettings(array $settings, callable $function)
    {
        // Prepare the settings
        $settings = Options::make($settings, [
            'hidden' => [
                'preFilter' => function ($v) {
                    return empty($v) ? false : $v;
                },
                'type' => ['bool', 'string', 'array'],
                'default' => false,
            ],
            'deleted' => [
                'preFilter' => function ($v) {
                    return empty($v) ? false : $v;
                },
                'type' => ['bool', 'string', 'array'],
                'default' => false,
            ],
        ]);
        
        // Check if we have work to do
        if ($settings['hidden'] === false && $settings['deleted'] === false) {
            return $function();
        }
        
        // Store the current filter to allow nesting
        $filterBackup = $this->filter;
        $cacheBackup = $this->enabledStateCache;
        $this->enabledStateCache = [];
        
        // Build the main query modifier with the given settings
        $this->filter = function (DataMapperQueryFilterEvent $event) use ($settings) {
            $model = get_class($event->getParentObject());
            $property = $event->getPropertyName();
            $querySettings = $event->getQuery()->getQuerySettings();
            
            // HIDDEN
            if ($this->validateEnabledState('hidden', $settings, $model, $property)) {
                $hidden = true;
                $querySettings
                    ->setIgnoreEnableFields(true)
                    ->setEnableFieldsToBeIgnored(['disabled']);
            }
            
            // DELETED
            if ($this->validateEnabledState('deleted', $settings, $model, $property)) {
                $deleted = true;
                $querySettings
                    ->setIgnoreEnableFields(true)
                    ->setIncludeDeleted(true);
            }
            
            // We have to manually parse the extbase query into a doctrine query
            // otherwise the restrictions will override our settings we set here.
            if (isset($deleted) || isset($hidden)) {
                $dQuery = BetterQueryTypo3DbQueryParserAdapter::getConcreteQueryParser()
                                                              ->convertQueryToDoctrineQueryBuilder($event->getQuery());
                
                if (! empty($deleted)) {
                    $dQuery->getRestrictions()->removeByType(DeletedRestriction::class);
                }
                
                if (! empty($hidden)) {
                    $dQuery->getRestrictions()->removeByType(HiddenRestriction::class);
                }
                
                $event->getQuery()->statement($dQuery);
            }
        };
        
        // Bind our event if required
        if (! $this->handlerRegistered) {
            $this->handlerRegistered = true;
            $this->eventBus->addListener(DataMapperQueryFilterEvent::class, [$this, 'onDataMapperQueryFilter']);
        }
        
        // Run our given function
        try {
            return $function();
        } finally {
            $this->filter = $filterBackup;
            $this->enabledStateCache = $cacheBackup;
        }
    }
    
    /**
     * Event listener to inject or special configuration into the generated query object
     *
     * @param   \LaborDigital\T3ba\Event\ExtBase\Persistence\DataMapperQueryFilterEvent  $event
     */
    public function onDataMapperQueryFilter(DataMapperQueryFilterEvent $event): void
    {
        // Ignore if there is no filter we have to apply
        if (empty($this->filter)) {
            return;
        }
        call_user_func($this->filter, $event);
    }
    
    /**
     * Internal helper that validates the given settings for the deleted and hidden relations
     * It will return true if the adjustments for the given $key have to be applied to the query settings
     *
     * @param   string  $key
     * @param   array   $settings
     * @param   string  $model
     * @param   string  $property
     *
     * @return bool
     */
    protected function validateEnabledState(string $key, array $settings, string $model, string $property): bool
    {
        $cacheKey = $key . '-' . $model . '-' . $property;
        if (isset($this->enabledStateCache[$cacheKey])) {
            return $this->enabledStateCache[$cacheKey];
        }
        $state = $settings[$key];
        $result = (static function () use ($state, $model, $property) {
            // Simple state
            if ($state === true) {
                return true;
            }
            if ($state === false) {
                return false;
            }
            
            // Check if a class name is given -> Allow all properties
            if (is_string($state)) {
                $state = [$state];
            }
            /** @noinspection InArrayMissUseInspection */
            if (in_array($model, $state, true)) {
                return true;
            }
            
            // Check if a specific property is allowed
            if (isset($state[$model])
                && ($state[$model] === $property
                    || (is_array($state[$model]) && in_array($property, $state[$model], true)))) {
                return true;
            }
            
            // Check if a class parent is allowed
            $parents = class_parents($model);
            foreach ($parents as $parent) {
                // Check if a class name is given -> Allow all properties
                /** @noinspection InArrayMissUseInspection */
                if (in_array($parent, $state, true)) {
                    return true;
                }
                
                // Check if a specific property is allowed
                if (isset($state[$model])
                    && ($state[$model] === $property
                        || (is_array($state[$model]) && in_array($property, $state[$model], true)))) {
                    return true;
                }
            }
            
            return false;
        })();
        
        return $this->enabledStateCache[$cacheKey] = $result;
    }
}
