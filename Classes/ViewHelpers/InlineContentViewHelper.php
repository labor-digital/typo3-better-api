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
 * Last modified: 2021.07.20 at 21:39
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ViewHelpers;


use Closure;
use LaborDigital\T3ba\Core\Di\StaticContainerAwareTrait;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

class InlineContentViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;
    use StaticContainerAwareTrait;
    
    protected $escapeOutput = false;
    
    public function initializeArguments()
    {
        $this->registerArgument('value', 'mixed', 'The incoming data to render, or null if VH children should be used');
    }
    
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    )
    {
        $uidList = static::findUidList($renderChildrenClosure());
        if (empty($uidList)) {
            return '';
        }
        
        return static::getCommonServices()->typoScript
            ->renderContentObject('RECORDS', [
                'tables' => 'tt_content',
                'source' => implode(',', $uidList),
                'dontCheckPid' => 1,
            ]);
    }
    
    /**
     * Tries to generate the list of uids based on a generic input
     *
     * @param $data
     *
     * @return array
     */
    protected static function findUidList($data): array
    {
        if (is_string($data)) {
            $uidList = Arrays::makeFromStringList($data);
        } elseif (is_iterable($data)) {
            $uidList = [];
            foreach ($data as $item) {
                $uidList[] = static::extractUidFromValue($item);
            }
        } else {
            $uidList = [static::extractUidFromValue($data)];
        }
        
        return array_filter($uidList);
    }
    
    /**
     * Tries to extract the uid from the given value.
     * Will throw an exception if it fails to extract a uid
     *
     * @param $value
     *
     * @return int
     */
    protected static function extractUidFromValue($value): int
    {
        if (empty($value)) {
            return 0;
        }
        
        if (is_numeric($value)) {
            return (int)$value;
        }
        
        if ($value instanceof AbstractEntity || is_callable([$value, 'getUid'])) {
            return $value->getUid();
        }
        
        if (is_array($value) && isset($value['uid'])) {
            return $value['uid'];
        }
        
        throw new \InvalidArgumentException('Inline content element renderer: Could not extract the uid of a given value');
    }
}