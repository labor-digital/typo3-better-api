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


namespace LaborDigital\T3ba\ExtConfig;


use LaborDigital\T3ba\Core\Di\DelegateContainer;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Core\Exception\T3baException;
use LaborDigital\T3ba\ExtConfig\SiteBased\SiteConfigContext;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use LaborDigital\T3ba\TypoContext\EnvFacet;
use Neunerlei\Configuration\Loader\ConfigContext;
use Neunerlei\PathUtil\Path;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;

class ExtConfigContext extends ConfigContext implements NoDiInterface
{
    /**
     * @var \LaborDigital\T3ba\ExtConfig\ExtConfigService
     */
    protected $extConfigService;
    
    /**
     * @var TypoContext
     */
    protected $typoContext;
    
    /**
     * Holds the cached namespace to ext key and vendor value map
     *
     * @var array
     */
    protected $extKeyVendorCache = [];
    
    /**
     * ExtConfigContext constructor.
     *
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigService  $extConfigService
     */
    public function __construct(ExtConfigService $extConfigService)
    {
        $this->extConfigService = $extConfigService;
    }
    
    /**
     * Returns the typo context instance if it was provided by the lifecycle yet
     *
     * @return \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     * @throws \LaborDigital\T3ba\ExtConfig\ExtConfigException
     */
    public function getTypoContext(): TypoContext
    {
        if (! $this->typoContext) {
            throw new ExtConfigException('You can\'t access the TypoContext object here, because it is to early in the lifecycle!');
        }
        
        return $this->typoContext;
    }
    
    /**
     * Allows to inject the typo context instance
     *
     * @param   \LaborDigital\T3ba\Tool\TypoContext\TypoContext  $typoContext
     *
     * @return $this
     */
    public function setTypoContext(TypoContext $typoContext): self
    {
        $this->typoContext = $typoContext;
        
        return $this;
    }
    
    /**
     * Returns multiple environment related constraint helpers
     *
     * @return \LaborDigital\T3ba\TypoContext\EnvFacet
     */
    public function env(): EnvFacet
    {
        if ($this->typoContext) {
            return $this->typoContext->env();
        }
        
        // Fallback if used to early in the lifecycle
        return new EnvFacet();
    }
    
    /**
     * Returns the instance of the ext config service
     *
     * @return \LaborDigital\T3ba\ExtConfig\ExtConfigService
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
     * @param   mixed  $raw  The value which should be traversed for markers
     *
     * @return mixed
     */
    public function replaceMarkers($raw)
    {
        if (is_array($raw)) {
            foreach ($raw as $k => $v) {
                $raw[$this->replaceMarkers($k)] = $this->replaceMarkers($v);
            }
        } elseif (is_string($raw)) {
            $markers = [
                '{{extKey}}' => $this->getExtKey(),
                '{{extKeyWithVendor}}' => $this->getExtKeyWithVendor(),
                '{{vendor}}' => $this->getVendor(),
            ];
            
            return str_ireplace(array_keys($markers), $markers, $raw);
        }
        
        return $raw;
    }
    
    /**
     * This helper allows you to resolve either a single pid entry or a list of multiple pids at once.
     * It will also take replaceMarkers into account before requesting the pids
     *
     * @param   string|int|array  $keys            Either the single key or an array of keys to retrieve
     * @param   int               $fallback        A fallback to use if the pid was not found.
     *                                             If not given, the method will throw an exception on a missing pid
     * @param   string|null       $siteIdentifier  An optional site identifier to resolve the pids with
     *
     * @return array|int
     * @see \LaborDigital\T3ba\TypoContext\PidFacet::get()
     */
    public function resolvePids($keys, int $fallback = -1, ?string $siteIdentifier = null)
    {
        if (empty($keys)) {
            return $keys;
        }
        
        $keys = $this->replaceMarkers($keys);
        
        if (! $siteIdentifier && $this instanceof SiteConfigContext) {
            $siteIdentifier = $this->getSiteIdentifier();
        }
        
        if (is_array($keys)) {
            return $this->getTypoContext()->pid()->getMultiple($keys, $fallback, $siteIdentifier);
        }
        
        return $this->getTypoContext()->pid()->get($keys, $fallback, $siteIdentifier);
    }
    
    /**
     * Helper to resolve either a single or an array of table names into their real table name.
     * It will unfold "..." prefixed table names to a valid ext base table name, or convert
     * table/model class names to their table name using NamingUtil
     *
     * @param   array|string|object  $tableName
     *
     * @return array|string
     * @see NamingUtil::resolveTableName()
     */
    public function resolveTableName($tableName)
    {
        if (is_array($tableName)) {
            return array_map([$this, 'resolveTableName'], $tableName);
        }
        
        if (is_string($tableName) && strpos($tableName, '...') === 0) {
            return implode('_', array_filter([
                'tx',
                NamingUtil::flattenExtKey($this->getExtKey()),
                'domain',
                'model',
                substr($tableName, 3),
            ]));
        }
        
        return NamingUtil::resolveTableName($tableName);
    }
    
    /**
     * Helper to resolve a file name into a valid filename to use in the TYPO3 ecosystem.
     * $filename can be either an ABSOLUTE path, a RELATIVE path (starting with ./) or a path
     * that begins with EXT:ExtKey... in order to be resolved.
     *
     * @param   string  $filename              The path to resolve
     * @param   bool    $asTypoPathIfPossible  If set to FALSE we will NOT try to resolve an EXT:ExtKey path for relatives or absolutes
     *
     * @return string
     * @throws \LaborDigital\T3ba\ExtConfig\ExtConfigException
     */
    public function resolveFilename(string $filename, bool $asTypoPathIfPossible = true): string
    {
        $filename = Path::normalize($this->replaceMarkers($filename));
        
        if (str_starts_with($filename, './') || str_starts_with($filename, '.' . DIRECTORY_SEPARATOR)) {
            if ($this->getExtKey() === 'LIMBO') {
                throw new ExtConfigException('Could not resolve relative filename: "' . $filename . '" because there is currently no active extension context');
            }
            
            $filename = 'EXT:' . $this->getExtKey() . substr($filename, 1);
        }
        
        if (! $asTypoPathIfPossible) {
            return $this->getTypoContext()->path()->typoPathToRealPath($filename);
        }
        
        if (! str_starts_with(strtolower($filename), 'ext:')) {
            try {
                return $this->getTypoContext()->path()->realPathToTypoExt($filename);
            } catch (T3baException $e) {
                // If the resolution into an TYPO3 path fails, we simply return the absolute path
            }
        }
        
        return $filename;
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
     * Returns the instance of the dependency injection container
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return DelegateContainer::getInstance();
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
