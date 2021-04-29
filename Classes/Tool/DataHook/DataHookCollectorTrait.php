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


namespace LaborDigital\T3BA\Tool\DataHook;


use InvalidArgumentException;

trait DataHookCollectorTrait
{
    /**
     * The list of registered data hook handlers
     *
     * @var array
     */
    protected $dataHooks = [];
    
    /**
     * Should return the field constraint (if any is required) or an empty array if no field constraint is required
     *
     * @return array
     */
    abstract protected function getDataHookTableFieldConstraints(): array;
    
    /**
     * Registers a new save data hook which allows you to filter incoming data when it is processed by the data handler.
     *
     * NOTE: This is a shortcut for registerDataHook(DataHookTypes::TYPE_SAVE, ...)
     *
     * @param   string  $handlerClass       The class you want to register as handler
     * @param   string  $handlerMethodName  The name of the method to execute on our handler class.
     *                                      The method will receive the DataHookContext object for the registered
     *                                      constraints as parameter.
     *
     * @return $this
     */
    public function registerSaveHook(
        string $handlerClass,
        string $handlerMethodName = 'saveHook'
    )
    {
        return $this->registerDataHook(DataHookTypes::TYPE_SAVE, $handlerClass, $handlerMethodName);
    }
    
    /**
     * Registers a new form data hook, which allows you to modify the field data or configuration when the backend
     * form engine builds the form for the data.
     *
     * NOTE: This is a shortcut for registerDataHook(DataHookTypes::TYPE_FORM, ...)
     *
     * @param   string  $handlerClass       The class you want to register as handler
     * @param   string  $handlerMethodName  The name of the method to execute on our handler class.
     *                                      The method will receive the DataHookContext object for the registered
     *                                      constraints as parameter.
     *
     * @return $this
     */
    public function registerFormHook(
        string $handlerClass,
        string $handlerMethodName = 'formHook'
    )
    {
        return $this->registerDataHook(DataHookTypes::TYPE_FORM, $handlerClass, $handlerMethodName);
    }
    
    /**
     * Registers a new data hook handler that should be processed when the data handler or the form engine processes
     * the data in some form.
     *
     * @param   string  $type               One of the DataHookTypes::TYPE_ constants to define which hook you want to
     *                                      listen to.
     * @param   string  $handlerClass       The class you want to register as handler
     * @param   string  $handlerMethodName  The name of the method to execute on our handler class.
     *                                      The method will receive the DataHookContext object for the registered
     *                                      constraints as parameter.
     * @param   array   $options            Additional options for this data hook.
     *
     * @return $this
     * @see \LaborDigital\T3BA\Tool\DataHook\DataHookTypes
     */
    public function registerDataHook(
        string $type,
        string $handlerClass,
        string $handlerMethodName = 'dataHook',
        array $options = []
    )
    {
        $this->validateDataHookType($type);
        $options['constraints'] = $this->getDataHookTableFieldConstraints();
        
        if (method_exists($this, 'additionalDataHookOptions')) {
            $options = array_merge($this->additionalDataHookOptions(), $options);
        }
        
        $this->dataHooks[$type][md5($handlerClass . '.' . $handlerMethodName)] = [
            [$handlerClass, $handlerMethodName],
            $options,
        ];
        
        return $this;
    }
    
    /**
     * Completely removes all registered data hooks
     *
     * @return $this
     */
    public function clearDataHooks()
    {
        $this->dataHooks = [];
        
        return $this;
    }
    
    /**
     * Removes a previously registered data hook handler from the list
     *
     * @param   string  $type               One of the DataHookTypes::TYPE_ constants to define the hook from which the
     *                                      handler should be removed again.
     * @param   string  $handlerClass       The name of the class to remove as a handler
     * @param   string  $handlerMethodName  The handler method that should be removed
     *
     * @return $this
     */
    public function removeDataHook(
        string $type,
        string $handlerClass,
        string $handlerMethodName = 'dataHook'
    )
    {
        $this->validateDataHookType($type);
        unset($this->dataHooks[$type][md5($handlerClass . '.' . $handlerMethodName)]);
        
        return $this;
    }
    
    /**
     * Returns the list of all registered data hooks. This is mostly internal to extract the values of this trait..
     *
     * @return array
     */
    public function getRegisteredDataHooks(): array
    {
        return array_map('array_values', $this->dataHooks);
    }
    
    /**
     * This helper allows to reset all registered hooks of this trait to the provided list.
     * The hook list is automatically validated and only valid hooks are added back to the store
     *
     * @param   array  $source  The source configuration to find the data hooks on.
     */
    protected function loadDataHooks(array $source): void
    {
        if (empty($source[DataHookTypes::TCA_DATA_HOOK_KEY])
            || ! is_array($source[DataHookTypes::TCA_DATA_HOOK_KEY])) {
            return;
        }
        
        $this->dataHooks = [];
        
        $constraint = $this->getDataHookTableFieldConstraints();
        
        foreach ($source[DataHookTypes::TCA_DATA_HOOK_KEY] as $type => $hooks) {
            if (! is_array($hooks) || ! is_string($type)) {
                continue;
            }
            
            try {
                $this->validateDataHookType($type);
            } catch (InvalidArgumentException $e) {
                continue;
            }
            
            foreach ($hooks as $def) {
                if (! isset($def[0], $def[1], $def[1]['constraints'])
                    || ! is_array($def)
                    || ! is_array($def[0])
                    || ! is_array($def[1])
                    || ! is_array($def[1]['constraints'])
                    || count($def[0]) !== 2) {
                    continue;
                }
                
                /** @noinspection TypeUnsafeComparisonInspection */
                if ($def[1]['constraints'] != $constraint) {
                    // @todo We could add a "partial" validation here,
                    // so only fields that are in the local constraints are evaluated
                    // This would have the added benefit, that non-type-validations are simply ignored
                    continue;
                }
                
                $this->dataHooks[$type][md5(implode('.', $def[0]))] = [
                    array_values($def[0]),
                    $def[1],
                ];
            }
        }
    }
    
    /**
     * Dumps the registered data hooks into the given $target array.
     * The hooks will be stored at the DataHookTypes::TCA_DATA_HOOK_KEY key
     *
     * @param   array  $target
     */
    protected function dumpDataHooks(array &$target): void
    {
        $hooks = $this->getRegisteredDataHooks();
        if (! empty($hooks)) {
            $target[DataHookTypes::TCA_DATA_HOOK_KEY] = $hooks;
        }
    }
    
    /**
     * Checks if the given type is valid or throws an invalid argument exception
     *
     * @param   string  $type
     */
    protected function validateDataHookType(string $type): void
    {
        $validTypes = [
            DataHookTypes::TYPE_SAVE,
            DataHookTypes::TYPE_SAVE_LATE,
            DataHookTypes::TYPE_SAVE_AFTER_DB,
            DataHookTypes::TYPE_MOVE,
            DataHookTypes::TYPE_DELETE,
            DataHookTypes::TYPE_RESTORE,
            DataHookTypes::TYPE_FORM,
            DataHookTypes::TYPE_INLINE_LOCALIZE_SYNC,
            DataHookTypes::TYPE_LOCALIZE,
            DataHookTypes::TYPE_VERSION,
        ];
        
        if (method_exists($this, 'getAdditionalValidDataHookTypes')) {
            $validTypes = array_merge($validTypes, $this->getAdditionalValidDataHookTypes());
        }
        
        if (! in_array($type, $validTypes, true)) {
            throw new InvalidArgumentException(
                'The given type: "' . $type . '" is invalid! Only the following types are allowed: ' .
                implode(',', $validTypes));
        }
    }
}
