<?php
/*
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
 * Last modified: 2020.08.24 at 19:57
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig;


use Neunerlei\Configuration\Loader\ConfigContext;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;

class ExtConfigContext extends ConfigContext
{
    /**
     * @var \LaborDigital\T3BA\Core\ExtConfig\ExtConfigService
     */
    protected $extConfigService;

    /**
     * Holds the cached namespace to ext key and vendor value map
     *
     * @var array
     */
    protected $extKeyVendorCache = [];

    /**
     * ExtConfigContext constructor.
     *
     * @param   \LaborDigital\T3BA\Core\ExtConfig\ExtConfigService  $extConfigService
     */
    public function __construct(ExtConfigService $extConfigService)
    {
        $this->extConfigService = $extConfigService;
    }

    /**
     * Returns the instance of the ext config service
     *
     * @return \LaborDigital\T3BA\Core\ExtConfig\ExtConfigService
     */
    public function getExtConfigService(): ExtConfigService
    {
        return $this->extConfigService;
    }

    /**
     * Returns the vendor key of the current configuration or an empty string
     *
     * @return string
     */
    public function getVendor(): ?string
    {
        return $this->getExtKeyAndVendorFromNamespace()[0];
    }

    /**
     * Returns the extension key for the current configuration
     *
     * @return string
     */
    public function getExtKey(): string
    {
        return $this->getExtKeyAndVendorFromNamespace()[1];
    }

    /**
     * Returns the extension key and the vendor, separated by a dot
     *
     * @return string
     */
    public function getExtKeyWithVendor(): string
    {
        return ($this->getVendor() === '' ? '' : $this->getVendor() . '.') . $this->getExtKey();
    }

    /**
     * This helper can be used to replace {{extKey}}, {{extKeyWithVendor}} and {{vendor}}
     * inside of keys and values with the proper value for the current context
     *
     * @param   array|mixed  $raw  The value which should be traversed for markers
     *
     * @return array|mixed
     */
    public function replaceMarkers($raw)
    {
        if (is_array($raw)) {
            foreach ($raw as $k => $v) {
                $raw[$this->replaceMarkers($k)] = $this->replaceMarkers($v);
            }
        } elseif (is_string($raw)) {
            $markers = [
                '{{extKey}}'           => $this->getExtKey(),
                '{{extKeyWithVendor}}' => $this->getExtKeyWithVendor(),
                '{{vendor}}'           => $this->getVendor(),
            ];

            return str_ireplace(array_keys($markers), $markers, $raw);
        }

        return $raw;
    }

    /**
     * Can be used to execute a given $callback in the scope of another extKey / vendor pair.
     * The current context"s extKey and vendor will be stored changed with the given values and reverted
     * to the initial state after the callback finished.
     *
     * @param   string       $extKey    An ext key to override the current one with
     * @param   string|null  $vendor    A vendor key to override the current one with
     * @param   callable     $callback  The callback to execute in the changed extKey/vendor scope
     *
     * @return mixed
     */
    public function runWithExtKeyAndVendor(string $extKey, ?string $vendor, callable $callback)
    {
        $namespace = empty($vendor) ? $extKey : $vendor . '.' . $extKey;

        return $this->runWithNamespace($namespace, $callback);
    }

    /**
     * Returns the instance of the TYPO3 package configuration for the currently configured extension
     *
     * @return \TYPO3\CMS\Core\Package\Package
     */
    public function getPackage(): Package
    {
        return $this->getLoaderContext()
                    ->getInstance(PackageManager::class)
                    ->getPackage($this->getExtKey());
    }

    /**
     * Extracts ext key and vendor from the currently set configuration namespace
     *
     * @return array
     */
    protected function getExtKeyAndVendorFromNamespace(): array
    {
        $namespace = $this->getNamespace();
        if (isset($this->extKeyVendorCache[$namespace])) {
            return $this->extKeyVendorCache[$namespace];
        }

        $parts = explode('.', $namespace);

        return $this->extKeyVendorCache[$namespace] = [
            isset($parts[1]) ? (string)$parts[0] : null,
            $parts[1] ?? $parts[0],
        ];
    }
}
