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

namespace LaborDigital\T3BA\ExtBase\Domain\ExtendedRelation;

use LaborDigital\Typo3BetterApi\Event\Events\DataMapperQueryFilterEvent;
use Neunerlei\EventBus\EventBusInterface;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class ExtendedRelationService
 *
 * @package LaborDigital\T3BA\ExtBase\Domain\ExtendedRelation
 *
 * @todo    finalize migrating this!
 */
class ExtendedRelationService implements SingletonInterface
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
     * @var \Neunerlei\EventBus\EventBusInterface
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
     * @param   \Neunerlei\EventBus\EventBusInterface  $eventBus
     */
    public function __construct(EventBusInterface $eventBus)
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
            $model = $event->getParentObject();
            $property = $event->getPropertyName();
            $querySettings = $event->getQuery()->getQuerySettings();
            
            // HIDDEN
            if ($this->validateEnabledState('hidden', $settings, $model, $property)) {
                $querySettings
                    ->setIgnoreEnableFields(true)
                    ->setEnableFieldsToBeIgnored(['hidden', 'disabled']);
            }
            
            // DELETED
            if ($this->validateEnabledState('deleted', $settings, $model, $property)) {
                $querySettings
                    ->setIgnoreEnableFields(true)
                    ->setIncludeDeleted(true);
            }
        };
        
        // Bind our event if required
        if (! $this->handlerRegistered) {
            $this->handlerRegistered = true;
            $this->eventBus->addListener(DataMapperQueryFilterEvent::class, [$this, '__dataMapperQueryFilter']);
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
     * @param   \LaborDigital\Typo3BetterApi\Event\Events\DataMapperQueryFilterEvent  $event
     */
    public function __dataMapperQueryFilter(DataMapperQueryFilterEvent $event)
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
