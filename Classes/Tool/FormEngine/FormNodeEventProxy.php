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
 * Last modified: 2020.03.20 at 16:40
 */

namespace LaborDigital\T3ba\Tool\FormEngine;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Event\FormEngine\BackendFormNodeFilterEvent;
use LaborDigital\T3ba\Event\FormEngine\BackendFormNodePostProcessorEvent;
use LaborDigital\T3ba\Tool\OddsAndEnds\InternalAccessTrait;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FormNodeEventProxy
 * This proxy is wrapped around every form node that is created by our extended node factory.
 * It is used to allow filtering of the form node before it is rendered.
 *
 * @package LaborDigital\T3ba\Tool\FormEngine
 */
class FormNodeEventProxy extends AbstractNode implements NoDiInterface
{
    use InternalAccessTrait;
    
    /**
     * The instance of the real node
     *
     * @var AbstractNode
     */
    protected $node;
    
    /**
     * @var TypoEventBus
     */
    protected $eventBus;
    
    /**
     * @inheritDoc
     */
    public function render()
    {
        return $this->eventBus->dispatch(
            new BackendFormNodePostProcessorEvent(
                $this, $this->node, $this->eventBus->dispatch(
                    new BackendFormNodeFilterEvent($this, $this->node, null)
                )->getResult() ?? $this->node->render()
            )
        )->getResult();
    }
    
    /**
     * Returns the data of this field
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data ?? [];
    }
    
    /**
     * Used to update the raw data array of this field
     *
     * @param   array  $data
     *
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        
        return $this;
    }
    
    /**
     * Returns the field configuration array
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->getData()['parameterArray']['fieldConf']['config'] ?? [];
    }
    
    /**
     * Used to update the field configuration
     *
     * @param   array  $config
     *
     * @return $this
     */
    public function setConfig(array $config): self
    {
        $this->data['parameterArray']['fieldConf']['config'] = $config;
        
        return $this;
    }
    
    /**
     * Creates a new node proxy instance
     *
     * @param   \LaborDigital\T3ba\Core\EventBus\TypoEventBus  $eventBus
     * @param   \TYPO3\CMS\Backend\Form\NodeFactory            $nodeFactory
     * @param   \TYPO3\CMS\Backend\Form\AbstractNode           $node
     *
     * @return \LaborDigital\T3ba\Tool\FormEngine\FormNodeEventProxy
     */
    public static function makeInstance(
        TypoEventBus $eventBus,
        NodeFactory $nodeFactory,
        AbstractNode $node
    ): FormNodeEventProxy
    {
        $i = GeneralUtility::makeInstance(static::class, $nodeFactory, []);
        $i->eventBus = $eventBus;
        $i->node = $node;
        $i->data = &$node->data;
        
        return $i;
    }
    
    /**
     * @inheritDoc
     */
    public function getExecutionTarget(): AbstractNode
    {
        return $this->node;
    }
}
