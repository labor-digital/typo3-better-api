<?php
/*
 * Copyright 2022 LABOR.digital
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
 * Last modified: 2022.06.21 at 14:56
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\FormEngine\Field;


use LaborDigital\T3ba\Tool\FormEngine\Custom\Field\AbstractCustomField;

class InformationField extends AbstractCustomField
{
    protected const TPL
        = <<<HTML
<div class="form-information text-muted" style="max-width: 600px">
    {label -> f:format.raw()}
</div>
HTML;
    
    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $this->context->setApplyOuterWrap(false);
        
        $labelOrTemplate = $this->context->getOption('lot');
        
        $translator = $this->cs()->translator;
        if ($translator->isTranslatable($labelOrTemplate)) {
            return $this->renderInternal($translator->translateBe($labelOrTemplate), null);
        }
        
        if (str_contains($labelOrTemplate, '/') || str_contains($labelOrTemplate, '\\')) {
            return $this->renderInternal(null, $this->cs()->typoContext->path()->typoPathToRealPath($labelOrTemplate));
        }
        
        return $this->renderInternal($labelOrTemplate, null);
    }
    
    protected function renderInternal(?string $label, ?string $template): string
    {
        return $this->renderFluidTemplate($template ?? static::TPL, ['label' => $label]);
    }
    
}