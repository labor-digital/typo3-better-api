<?php
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
 * Last modified: 2020.03.21 at 21:11
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig;

use LaborDigital\Typo3BetterApi\BackendForms\TableSqlGenerator;
use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionService;
use LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionRegistry;
use LaborDigital\Typo3BetterApi\ExtConfig\OptionList\ExtConfigOptionList;
use LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;

/**
 * Class ExtConfigContext
 *
 * @package LaborDigital\Typo3BetterApi\ExtConfig
 *
 * @property TableSqlGenerator          $SqlGenerator
 * @property DataHandlerActionService   $DataHandlerActions
 * @property TypoContext                $TypoContext
 * @property TempFs                     $Fs
 * @property ExtConfigOptionList        $OptionList
 * @property ExtConfigExtensionRegistry $ExtensionRegistry
 */
class ExtConfigContext
{
    use CommonServiceLocatorTrait;
    use ExtConfigContextPublicServiceTrait {
        ExtConfigContextPublicServiceTrait::getInstanceOf insteadof CommonServiceLocatorTrait;
        ExtConfigContextPublicServiceTrait::injectContainer insteadof CommonServiceLocatorTrait;
    }
    
    /**
     * The vendor name for the currently configured extension
     *
     * @var string
     */
    protected $vendor = 'LIMBO';
    
    /**
     * The extKey for the currently configured extension
     *
     * @var string
     */
    protected $extKey = 'LIMBO';
    
    /**
     * ExtConfigContext constructor.
     *
     * @param   \LaborDigital\Typo3BetterApi\TypoContext\TypoContext  $context
     */
    public function __construct(TypoContext $context)
    {
        $this->setServiceInstance(TempFs::class, TempFs::makeInstance('extConfig'));
        $this->setServiceFactory(ExtConfigExtensionRegistry::class, function (TypoContainerInterface $container) {
            return $container->get(ExtConfigExtensionRegistry::class, ['args' => [$this]]);
        });
        $this->addToServiceMap([
            'SqlGenerator'       => function () {
                return $this->SqlGenerator();
            },
            'DataHandlerActions' => function () {
                return $this->DataHandlerActions();
            },
            'TypoContext'        => $context,
            'Fs'                 => $this->getService(TempFs::class),
            'ExtensionRegistry'  => function () {
                return $this->ExtensionRegistry();
            },
        ]);
    }
    
    /**
     * Returns the vendor key of the current configuration or an empty string
     *
     * @return string
     */
    public function getVendor(): string
    {
        return $this->vendor;
    }
    
    /**
     * Can be used to set the vendor name of the current configuration
     *
     * @param   string  $vendor
     */
    public function setVendor(string $vendor): void
    {
        $this->vendor = $vendor;
    }
    
    /**
     * Returns the extension key for the current configuration
     *
     * @return string
     */
    public function getExtKey(): string
    {
        return $this->extKey;
    }
    
    /**
     * Is used to set the extension key for the current configuration
     *
     * @param   string  $extKey
     */
    public function setExtKey(string $extKey): void
    {
        $this->extKey = $extKey;
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
        // Store old context values
        $backupExtKey = $this->getExtKey();
        $backupVendor = $this->getVendor();
        
        // Update the context
        $this->setExtKey($extKey);
        $this->setVendor((string)$vendor);
        
        // Call the callback
        $result = call_user_func($callback);
        
        // Restore the context
        $this->setExtKey($backupExtKey);
        $this->setVendor($backupVendor);
        
        // Done
        return $result;
    }
    
    /**
     * This method does essentially the same as runWithExtKeyAndVendor() but receives a "data" array
     * that is passed to the extConfig's getCachedValueOrRun() callbacks.
     *
     * It will automatically iterate over all elements in data and call the given $callback once,
     * for each entry. Every time the callback is updated the extkey and vendor stored by the cached
     * value is set for this context.
     *
     * The given callback will receive the $value and $key as arguments
     *
     * @param   array     $data
     * @param   callable  $callback
     */
    public function runWithCachedValueDataScope(array $data, callable $callback)
    {
        foreach ($data as $k => $el) {
            $this->runWithExtKeyAndVendor($el['extKey'], $el['vendor'], function () use ($callback, $el, $k) {
                call_user_func($callback, $el['value'], $k);
            });
        }
    }
    
    /**
     * This method is quite similar to runWithCachedValueDataScope(). The main and only difference is,
     * that it only uses the given $data stack to retrieve the first possible ext key and vendor to hydrate the context,
     * before calling the given callback.
     *
     * @param   array     $data
     * @param   callable  $callback
     *
     * @return mixed|null
     * @see runWithCachedValueDataScope()
     */
    public function runWithFirstCachedValueDataScope(array $data, callable $callback)
    {
        foreach ($data as $k => $el) {
            return $this->runWithExtKeyAndVendor($el['extKey'], $el['vendor'], function () use ($callback, $el, $k) {
                return call_user_func($callback, $el['value'], $k);
            });
        }
        
        return null;
    }
    
    /**
     * Internal helper to bind the option list as late property
     *
     * @param $optionList
     *
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     */
    public function __injectOptionList($optionList)
    {
        if (! $optionList instanceof ExtConfigOptionList) {
            throw new ExtConfigException('The given option list is not valid!');
        }
        $this->addToServiceMap(['OptionList' => $optionList]);
    }
}
