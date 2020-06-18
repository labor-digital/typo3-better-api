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
 * Last modified: 2020.03.20 at 16:40
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\Addon;

use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\CoreModding\InternalAccessTrait;
use LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodeFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodePostProcessorEvent;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\NodeFactory;

/**
 * Class FormNodeEventProxy
 * This proxy is wrapped around every form node that is created by our extended node factory.
 * It is used to allow filtering of the form node before it is rendered.
 *
 * @package LaborDigital\Typo3BetterApi\BackendForms\Addons
 */
class FormNodeEventProxy extends AbstractNode
{
    use InternalAccessTrait;
    
    /**
     * The instance of the real node
     *
     * @var AbstractNode
     */
    protected $node;
    
    /** @noinspection PhpMissingParentConstructorInspection */
    /**
     * @inheritDoc
     */
    public function __construct(?NodeFactory $nodeFactory = null, ?array $data = null)
    {
    }
    
    /**
     * @inheritDoc
     */
    public function render()
    {
        // Emit the event for other renderers to hook into
        TypoEventBus::getInstance()->dispatch(($e = new BackendFormNodeFilterEvent($this, $this->node, null)));
        $result = $e->getResult();
        
        // Use fallback if we did not get a result from the event
        if ($result === null) {
            $result = $this->node->render();
        }
        
        // Allow late filtering
        TypoEventBus::getInstance()->dispatch(($e = new BackendFormNodePostProcessorEvent($this, $this->node,
            $result)));
        
        return $e->getResult();
    }
    
    /**
     * Creates a new node proxy instance
     *
     * @param   \TYPO3\CMS\Backend\Form\AbstractNode  $node
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\Addon\FormNodeEventProxy
     */
    public static function makeInstance(AbstractNode $node): FormNodeEventProxy
    {
        $i       = TypoContainer::getInstance()->get(static::class, ['args' => [null, null]]);
        $i->node = $node;
        $i->data = &$node->data;
        
        return $i;
    }
    
    /**
     * @inheritDoc
     */
    public function getExecutionTarget()
    {
        return $this->node;
    }
}
