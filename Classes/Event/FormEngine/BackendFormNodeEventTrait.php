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

namespace LaborDigital\T3ba\Event\FormEngine;

use LaborDigital\T3ba\Tool\FormEngine\FormNodeEventProxy;
use TYPO3\CMS\Backend\Form\AbstractNode;

trait BackendFormNodeEventTrait
{
    
    /**
     * The instance of the proxy that dispatched this event
     *
     * @var \LaborDigital\T3ba\Tool\FormEngine\FormNodeEventProxy
     */
    protected $proxy;
    
    /**
     * The instance of the real node that should be rendered
     *
     * @var \TYPO3\CMS\Backend\Form\AbstractNode
     */
    protected $node;
    
    /**
     * The rendered result. If null the node's render() method will be executed, if it is a array
     * the given data will be passed on directly
     *
     * @var array|null
     */
    protected $result;
    
    /**
     * BackendFormNodeFilterEvent constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\FormEngine\FormNodeEventProxy  $proxy
     * @param   \TYPO3\CMS\Backend\Form\AbstractNode                   $node
     * @param   array|null                                             $result
     */
    public function __construct(FormNodeEventProxy $proxy, AbstractNode $node, ?array $result)
    {
        $this->proxy = $proxy;
        $this->node = $node;
        $this->result = $result;
    }
    
    /**
     * Returns the instance of the proxy that dispatched this event
     *
     * @return \LaborDigital\T3ba\Tool\FormEngine\FormNodeEventProxy
     */
    public function getProxy(): FormNodeEventProxy
    {
        return $this->proxy;
    }
    
    /**
     * Returns the instance of the real node that should be rendered
     *
     * @return \TYPO3\CMS\Backend\Form\AbstractNode
     */
    public function getNode(): AbstractNode
    {
        return $this->node;
    }
    
    /**
     * Returns the rendered result. If null the node's render() method will be executed, if it is a string
     * the given string will be passed on directly
     *
     * @return array|null
     */
    public function getResult(): ?array
    {
        return $this->result;
    }
    
    /**
     * Used to update the rendered result. If null the node's render() method will be executed, if it is a string
     * the given string will be passed on directly
     *
     * @param   array|null  $result
     *
     * @return $this
     */
    public function setResult(?array $result): self
    {
        $this->result = $result;
        
        return $this;
    }
}
