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


namespace LaborDigital\T3ba\ExtBase\Controller;


use LaborDigital\T3ba\Tool\BackendPreview\BackendPreviewRendererContext;
use LaborDigital\T3ba\Tool\Tca\ContentType\Domain\AbstractDataModel;
use LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ContentRepository;

trait ContentControllerDataTrait
{
    /**
     * Internal helper to cache the data locally
     *
     * @internal
     *
     * @var array
     */
    protected $__dataCache = [];
    
    /**
     * Returns the raw row in tt_content including the extension fields if there are any.
     *
     * @return array
     */
    protected function getData(): array
    {
        if ($this->__dataCache['raw']) {
            return $this->__dataCache['raw'];
        }
        
        ControllerUtil::requireActionController($this);
    
        $row = [];
        
        // Interop helper when used in combination with the ContentControllerBackendPreviewTrait
        if (isset($this->previewRendererContext)
            && $this->previewRendererContext instanceof BackendPreviewRendererContext) {
            $row = $this->previewRendererContext->getRow();
        } elseif(isset($this->configurationManager)) {
            $row = $this->configurationManager->getContentObject()->data;
        }
        
        return $this->__dataCache['raw'] = $this->getContentRepository()->getExtendedRow($row);
    }
    
    /**
     * Returns a new instance of the data model for the tt_content record linked to this content element/plugin
     * Note: The data is cached locally, use resetData() to reset the internal cache
     *
     * @return mixed
     */
    protected function getDataModel(): AbstractDataModel
    {
        if ($this->__dataCache['model']) {
            return $this->__dataCache['model'];
        }
        
        ControllerUtil::requireActionController($this);
        
        return $this->__dataCache['model']
            = $this->getContentRepository()->hydrateModel($this->getData());
    }
    
    /**
     * Resets the locally resolved data for both the model and row.
     * After you called this and execute getData() or  getDataModel() again, the data is refetched
     * from it's source
     */
    protected function resetData(): void
    {
        $this->__dataCache = [];
    }
    
    /**
     * Returns the instance of the content repository, to read and write the tt_content records with.
     *
     * @return \LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ContentRepository
     */
    protected function getContentRepository(): ContentRepository
    {
        ControllerUtil::requireActionController($this);
        
        return $this->getService(ContentRepository::class);
    }
}
