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
 * Last modified: 2021.02.15 at 13:21
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ViewHelpers\Link;


use Closure;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

class UriViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    public function initializeArguments()
    {
        $this->registerArgument(
            'definition',
            'string',
            'The key/name of the link definition we should generate the uri for',
            true
        );
        $this->registerArgument(
            'args',
            'mixed',
            'The list of arguments to pass to the link',
            false,
            null);
        $this->registerArgument(
            'fragments',
            'mixed',
            'The list of hash fragments to pass to the link',
            false,
            null);
        $this->registerArgument(
            'relative',
            'bool',
            'True to render a relative link instead of a absolute one',
            false,
            false
        );
    }

    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $link = TypoContext::getInstance()->di()->cs()->links->getLink(
            $arguments['definition'],
            $arguments['args'],
            $arguments['fragments'],
        );

        return $link->build([
            'relative' => $arguments['relative'],
        ]);
    }

}
