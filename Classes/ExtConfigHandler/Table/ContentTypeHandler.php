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
 * Last modified: 2021.07.02 at 18:42
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Table;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigHandler;
use LaborDigital\T3ba\ExtConfig\Traits\DelayedConfigExecutionTrait;
use LaborDigital\T3ba\Tool\BackendPreview\BackendListLabelRendererInterface;
use LaborDigital\T3ba\Tool\BackendPreview\BackendPreviewRendererInterface;
use Neunerlei\Configuration\Handler\HandlerConfigurator;

class ContentTypeHandler extends AbstractExtConfigHandler implements NoDiInterface
{
    use DelayedConfigExecutionTrait;
    
    protected $previewRenderers = [];
    protected $listLabelRenderers = [];
    
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $configurator->registerLocation('Configuration/ContentType');
        $configurator->registerInterface(ConfigureContentTypeInterface::class);
        $configurator->executeThisHandlerAfter(TcaTableHandler::class);
    }
    
    /**
     * @inheritDoc
     */
    public function prepare(): void { }
    
    /**
     * @inheritDoc
     */
    public function handle(string $class): void
    {
        $cType = $class::getCType();
    
        // Register backend preview renderers
        $interfaces = class_implements($class);
    
        if (in_array(BackendPreviewRendererInterface::class, $interfaces, true)) {
            $this->previewRenderers[] = [$class, ['CType' => $cType]];
        }
        if (in_array(BackendListLabelRendererInterface::class, $interfaces, true)) {
            $this->listLabelRenderers[] = [$class, ['CType' => $cType]];
        }
    
        $this->saveDelayedConfig($this->context, 'tca.contentTypes', $class, $cType);
    
    }
    
    /**
     * @inheritDoc
     */
    public function finish(): void
    {
        if (! empty($this->previewRenderers)) {
            $this->context->getState()->mergeIntoArray('t3ba.backendPreview.previewRenderers', $this->previewRenderers);
        }
        if (! empty($this->listLabelRenderers)) {
            $this->context->getState()->mergeIntoArray('t3ba.backendPreview.listLabelRenderers', $this->listLabelRenderers);
        }
    }
    
}