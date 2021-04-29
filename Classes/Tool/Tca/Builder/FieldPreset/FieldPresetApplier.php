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


namespace LaborDigital\T3BA\Tool\Tca\Builder\FieldPreset;


use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException;
use LaborDigital\T3BA\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\Configuration\State\LocallyCachedStatePropertyTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\SingletonInterface;

class FieldPresetApplier implements SingletonInterface, LoggerAwareInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;
    use TypoContextAwareTrait;
    use LocallyCachedStatePropertyTrait;
    
    /**
     * @var TcaBuilderContext
     */
    protected $context;
    
    /**
     * @var AbstractField
     */
    protected $field;
    
    /**
     * The list of presets that have been configured
     *
     * @var array
     */
    protected $presets = [];
    
    /**
     * TYPO3 container hook to be called when the object was created
     *
     * @internal
     * @private
     */
    public function initializeObject(): void
    {
        $this->registerCachedProperty('presets', 'tca.fieldPresets',
            $this->getTypoContext()->config()->getConfigState());
    }
    
    /**
     * Called when a field executed the "applyPreset" to set the context for the preset to be applied
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractField  $field
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext    $context
     *
     * @return $this
     *
     * @internal
     * @private
     */
    public function configureField(AbstractField $field, TcaBuilderContext $context): self
    {
        $this->field = $field;
        $this->context = $context;
        
        return $this;
    }
    
    /**
     * Returns true if a certain preset exists, false if not
     *
     * @param   string  $key
     *
     * @return bool
     */
    public function hasPreset(string $key): bool
    {
        return isset($this->presets[$key]);
    }
    
    /**
     * @throws \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException
     */
    public function __call($name, $arguments)
    {
        if (! $this->field || ! $this->context) {
            throw new TcaBuilderException('You can\'t apply a preset without configuring a field beforehand!');
        }
        
        if (! $this->hasPreset($name)) {
            $this->logger->error('Field preset applier failed to apply a preset with name: ' . $name
                                 . ' for field: ' . $this->field->getId() . ' because the preset was not registered!');
            
            return $this;
        }
        
        $definition = $this->presets[$name] ?? null;
        
        /** @var \LaborDigital\T3BA\Tool\Tca\Builder\FieldPreset\FieldPresetInterface $i */
        $i = $this->getService($definition[0]);
        $i->setContext($this->context);
        $i->setField($this->field);
        
        call_user_func_array([$i, $definition[1]], $arguments);
        
        return $this->field;
    }
    
    
}
