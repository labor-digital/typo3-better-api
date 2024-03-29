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


namespace LaborDigital\T3ba\ExtConfig\Interfaces;

/**
 * Interface SiteKeyProviderInterface
 *
 * NOTE: This interface is used for site-based configuration handlers.
 * In other handlers this interface is not used. It is optional to filter configs to apply only to specific
 * sites.
 *
 * @package LaborDigital\T3ba\ExtConfig\Interfaces
 */
interface SiteIdentifierProviderInterface
{
    
    /**
     * Must return the list of site identifiers this configuration applies to.
     * If an empty array is returned the configuration will be applied to all sites.
     *
     * @param   array  $existingSiteIdentifiers  Receives the list of registered site keys in this installation.
     *
     * @return array
     */
    public static function getSiteIdentifiers(array $existingSiteIdentifiers): array;
}
