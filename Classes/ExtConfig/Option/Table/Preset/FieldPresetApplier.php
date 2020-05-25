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
 * Last modified: 2020.03.21 at 16:51
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\Table\Preset;

use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormField;
use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionInterface;
use TYPO3\CMS\Core\SingletonInterface;

class FieldPresetApplier implements SingletonInterface
{
    use FieldPresetApplierTrait;
    
    /**
     * The list of preset applier instances to avoid creating multiple objects
     * @var \LaborDigital\Typo3BetterApi\BackendForms\FormPresets\FormPresetInterface[]
     */
    protected $instances = [];
    
    /**
     * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
     */
    protected $container;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
     */
    protected $context;
    
    /**
     * @var AbstractFormField
     */
    protected $field;
    
    /**
     * The instance of the trait generator -> always use getExtensionHandler()!
     * @var \LaborDigital\Typo3BetterApi\ExtConfig\Option\Table\Preset\FieldPresetApplierTraitGenerator|null
     */
    protected $extensionHandler;
    
    /**
     * FieldPresetApplier constructor.
     *
     * @param \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface $container
     */
    public function __construct(TypoContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Returns true if a certain preset exists, false if not
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasPreset(string $key): bool
    {
        return method_exists($this, $key) ||
            !empty($this->getExtensionHandler()->getClassNameForMethodName($key));
    }
    
    /**
     * Internal helper to inject the field the preset should be applied to
     *
     * @param \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormField $field
     * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext               $context
     */
    public function __setField(AbstractFormField $field, ExtConfigContext $context)
    {
        $this->field = $field;
        $this->context = $context;
    }
    
    /**
     * Fallback for all non existing presets
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     */
    public function __call($name, $arguments)
    {
        // Try to find a not-yet compiled preset -> When an extension is activated
        $class = $this->getExtensionHandler()->getClassNameForMethodName($name);
        if (!empty($class)) {
            return $this->callHandlerInstance($class, $name, $arguments);
        }
        
        // Not found
        throw new ExtConfigException("Could not apply preset $name, because it was not registered!");
    }
    
    /**
     * Returns the instance of the extension handler
     * This is used to check if a new option has been added (e.g. when the extension is activated)
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Table\Preset\FieldPresetApplierTraitGenerator
     */
    protected function getExtensionHandler(): FieldPresetApplierTraitGenerator
    {
        if (!empty($this->extensionHandler)) {
            return $this->extensionHandler;
        }
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->extensionHandler =
            $this->context->ExtensionRegistry->getExtensionHandler(ExtConfigExtensionInterface::TYPE_FORM_FIELD_PRESET);
    }
    
    /**
     * Internal helper to call the handler method of a certain instance
     *
     * @param string $className
     * @param string $methodName
     * @param array  $arguments
     *
     * @return mixed
     */
    protected function callHandlerInstance(string $className, string $methodName, array $arguments)
    {
        // Prepare the instance if required
        if (!isset($this->instances[$className])) {
            /** @var \LaborDigital\Typo3BetterApi\BackendForms\FormPresets\FormPresetInterface $i */
            $i = $this->container->get($className);
            $i->setContext($this->context);
            $this->instances[$className] = $i;
        }
        
        // Prepare the instance and call the required method
        $i = $this->instances[$className];
        $i->setField($this->field);
        call_user_func_array([$i, $methodName], $arguments);
        return $this->field;
    }
}
