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
 * Last modified: 2021.07.22 at 11:52
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ViewHelpers;


use Closure;
use LaborDigital\T3ba\Core\Di\StaticContainerAwareTrait;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

class PageTitleViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;
    use StaticContainerAwareTrait;
    
    public function initializeArguments()
    {
        $this->registerArgument('value', 'string', 'The page title to set, or null if VH children should be used');
    }
    
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    )
    {
        static::cs()->page->setPageTitle((string)$renderChildrenClosure());
    }
}