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
 * Last modified: 2021.11.15 at 12:42
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Event\ExtConfig;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use Neunerlei\Configuration\State\ConfigState;

/**
 * Emitted when the MAIN SITE-BASED ext config for A SINGLE SITE was generated.
 * Allows you to modify/filter the config state before it is persisted into the root state object
 */
class SingleSiteBasedExtConfigGeneratedEvent extends AbstractExtConfigGeneratedEvent
{
    /**
     * The site identifier for which the config was generated
     *
     * @var string
     */
    protected $identifier;
    
    public function __construct(string $identifier, ExtConfigContext $context, ConfigState $state)
    {
        parent::__construct($context, $state);
        $this->identifier = $identifier;
    }
    
    /**
     * Returns the site identifier for which the config was generated
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
    
}