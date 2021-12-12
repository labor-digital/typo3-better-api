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
 * Last modified: 2021.12.12 at 23:10
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ConfigurationModule;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\ConfigurationModule\ConfigureConfigurationModuleProviderInterface;
use LaborDigital\T3ba\ExtConfigHandler\ConfigurationModule\ProviderConfigurator;
use Neunerlei\Configuration\State\ConfigState;
use TYPO3\CMS\Lowlevel\ConfigurationModuleProvider\AbstractProvider;

class ExtConfigProvider extends AbstractProvider implements ConfigureConfigurationModuleProviderInterface
{
    
    /**
     * @var \Neunerlei\Configuration\State\ConfigState
     */
    protected ConfigState $configState;
    
    public function __construct(ConfigState $configState)
    {
        $this->configState = $configState;
    }
    
    /**
     * @inheritDoc
     */
    public static function getProviderIdentifier(): string
    {
        return 't3ba.extConfig';
    }
    
    /**
     * @inheritDoc
     */
    public static function configureProvider(ProviderConfigurator $configurator, ExtConfigContext $context): void
    {
        $configurator->setLabel('t3ba.lowLevel.configLabel');
    }
    
    /**
     * @inheritDoc
     */
    public function getConfiguration(): array
    {
        return $this->unpackJsonValues($this->configState->getAll());
    }
    
    /**
     * Internal helper to unpack all JSON entries in the given list
     *
     * @param   array  $list
     *
     * @return array
     */
    protected function unpackJsonValues(array $list): array
    {
        foreach ($list as $k => $v) {
            if (is_array($v)) {
                $list[$k] = $this->unpackJsonValues($v);
                continue;
            }
            
            if (! is_string($v)) {
                continue;
            }
            
            if (str_starts_with($v, '[') || str_starts_with($v, '{')) {
                try {
                    $list[$k] = array_merge(
                        ['@info' => 'This value is stored as JSON'],
                        json_decode($v, true, 512, JSON_THROW_ON_ERROR)
                    );
                } catch (\JsonException $e) {
                }
            }
        }
        
        return $list;
    }
    
}