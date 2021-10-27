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
 * Last modified: 2021.10.27 at 11:36
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\SpecialCase;


use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;
use TYPO3\CMS\Core\Resource\File;

class SysFileReferenceCase implements TcaSpecialCaseHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function provideTableNames(): array
    {
        return ['sys_file_reference'];
    }
    
    /**
     * @inheritDoc
     */
    public function initializeTca(array &$tca, TcaTable $table): void
    {
        foreach ($tca['types'] as $typeNum => &$type) {
            if (! $type['showitem']) {
                continue;
            }
            
            $type['showitem'] = str_replace(
                ';basicoverlayPalette',
                ';' . $this->getSpecialPaletteForTypeNum($typeNum),
                $type['showitem']
            );
        }
    }
    
    /**
     * @inheritDoc
     */
    public function dumpTca(array &$tca, TcaTable $table): void
    {
        foreach ($tca['types'] as $typeNum => &$type) {
            if (! $type['showitem']) {
                continue;
            }
            
            $type['showitem'] = str_replace(
                ';' . $this->getSpecialPaletteForTypeNum($typeNum),
                ';basicoverlayPalette',
                $type['showitem']
            );
        }
        
        dbge($tca);
    }
    
    /**
     * Returns the expected special case palette for the given type number
     *
     * @param   string|int  $typeNum  The numeric type value
     *
     * @return string
     */
    protected function getSpecialPaletteForTypeNum($typeNum): string
    {
        if ($typeNum === File::FILETYPE_VIDEO) {
            return 'videoOverlayPalette';
        }
        if ($typeNum === File::FILETYPE_AUDIO) {
            return 'audioOverlayPalette';
        }
        
        return 'imageoverlayPalette';
    }
}