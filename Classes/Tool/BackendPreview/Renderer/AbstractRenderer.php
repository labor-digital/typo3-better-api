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


namespace LaborDigital\T3ba\Tool\BackendPreview\Renderer;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\Tca\TcaUtil;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;
use Throwable;

abstract class AbstractRenderer implements PublicServiceInterface
{
    use TypoContextAwareTrait;
    use ContainerAwareTrait;
    
    /**
     * Internal helper that is used to resolve the default header based on the given database row.
     * If no header was found an empty string is returned
     *
     * @param   array  $row
     *
     * @return string
     */
    protected function findDefaultHeader(array $row): string
    {
        return TcaUtil::runWithResolvedTypeTca($row, 'tt_content', function () use ($row) {
            $translator = $this->cs()->translator;
            
            // Find for plugin
            if ($row['CType'] === 'list') {
                $signature = $row['list_type'];
                foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $listTypeItem) {
                    if ($listTypeItem[1] !== $signature) {
                        continue;
                    }
                    
                    return $translator->translateBe($listTypeItem[0]);
                }
                
                return '';
            }
            
            // Find for content element
            $signature = $row['CType'];
            foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $listTypeItem) {
                if ($listTypeItem[1] !== $signature) {
                    continue;
                }
                
                return $translator->translateBe($listTypeItem[0]);
            }
            
            return '';
        });
        
    }
    
    /**
     * Helper to render a given throwable as a readable string
     *
     * @param   \Throwable  $e
     *
     * @return string
     */
    protected function stringifyThrowable(Throwable $e): string
    {
        return
            (empty($e->getMessage()) ? get_class($e) : $e->getMessage()) .
            ' (' . $e->getFile() . ':' . $e->getLine() . ')';
    }
}
