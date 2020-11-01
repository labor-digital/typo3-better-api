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
 * Last modified: 2020.08.23 at 23:23
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event\FormEngine;

/**
 * Class BackendFormCustomElementPostProcessorEvent
 *
 * Emitted after the custom element controller was executed.
 * Allows to override or modify the rendered result
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class BackendFormCustomElementPostProcessorEvent
{

    /**
     * The context that was passed to the custom element controller
     *
     * @var \LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementContext
     */
    protected $context;

    /**
     * The prepared result array for the form engine
     *
     * @var array
     */
    protected $result;

    /**
     * BackendFormCustomElementPostProcessorEvent constructor.
     *
     * @param   \LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementContext  $context
     * @param   array                                                                          $result
     */
    public function __construct(CustomElementContext $context, array $result)
    {
        $this->context = $context;
        $this->result  = $result;
    }

    /**
     * Returns the context that was passed to the custom element controller
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementContext
     */
    public function getContext(): CustomElementContext
    {
        return $this->context;
    }

    /**
     * Returns the prepared result array for the form engine
     *
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * Sets the prepared result array for the form engine
     *
     * @param   array  $result
     *
     * @return BackendFormCustomElementPostProcessorEvent
     */
    public function setResult(array $result): BackendFormCustomElementPostProcessorEvent
    {
        $this->result = $result;

        return $this;
    }
}
