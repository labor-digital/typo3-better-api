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
 * Last modified: 2021.05.01 at 20:49
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Common\Assets;

use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigApplier;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Trait AssetApplierTrait
 *
 * MUST be applied in a class that extends AbstractExtConfigApplier
 *
 * @package LaborDigital\T3ba\ExtConfigHandler\Common\Assets
 * @see     AbstractExtConfigApplier
 */
trait AssetApplierTrait
{
    /**
     * Executed in an eventHandler of either BackendAssetFilterEvent or FrontendAssetFilterEvent to apply the stored asset definitions
     */
    protected function executeAssetCollectorActions(string $configPath): void
    {
        $list = $this->state->get($configPath . '.assets');
        if (! empty($list)) {
            $collector = GeneralUtility::makeInstance(AssetCollector::class);
            foreach (Arrays::makeFromJson($list) as $action) {
                [$method, $args] = $action;
                $collector->$method(...$args);
            }
        }
    }
}