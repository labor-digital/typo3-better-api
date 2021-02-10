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
 * Last modified: 2021.02.05 at 19:06
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Io;


use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;
use LaborDigital\T3BA\Core\TempFs\TempFs;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Flex;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\FlexField;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\FlexSection;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\FlexTab;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;

class Dumper implements PublicServiceInterface
{
    /**
     * @var \TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools
     */
    protected $tools;

    /**
     * @var \LaborDigital\T3BA\Core\TempFs\TempFs|null
     */
    protected $fs;

    /**
     * Dumper constructor.
     *
     * @param   \TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools  $tools
     * @param   \LaborDigital\T3BA\Core\TempFs\TempFs|null            $fs
     */
    public function __construct(FlexFormTools $tools, ?TempFs $fs = null)
    {
        $this->tools = $tools;
        $this->fs    = $fs ?? TempFs::makeInstance('FlexForm');
    }

    /**
     * Dumps the given flex form object back into an array
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Flex  $flex
     *
     * @return array
     */
    public function dumpAsArray(Flex $flex): array
    {
        $sheets          = [];
        $result          = [];
        $previousPointer = null;
        $pointer         = &$sheets;

        if (! empty($flex->getMeta())) {
            $result['meta'] = $flex->getMeta();
        }

        $result['sheets'] = &$sheets;

        foreach ($flex->getAllChildren() as $child) {
            if ($child instanceof FlexTab) {
                unset($tabEl);
                $tabEl = [];
                $tab   = [
                    'ROOT' => [
                        'type' => 'array',
                        'el'   => &$tabEl,
                    ],
                ];

                if (! empty($child->getLabel())) {
                    $tab['ROOT']['TCEforms']['sheetTitle'] = $child->getLabel();
                }
                if (! empty($child->getDisplayCondition())) {
                    $tab['ROOT']['TCEforms']['displayCond'] = $child->getDisplayCondition();
                }

                $sheets[$child->getId()] = $tab;
                $pointer                 = &$tabEl;
                continue;
            }

            if ($child instanceof FlexSection) {
                unset($sectionEl);
                $sectionEl = [];
                $section   = [
                    'section' => 1,
                    'type'    => 'array',
                    'el'      => [
                        $child->getContainerItemId() => [
                            'title' => $child->getContainerItemLabel(),
                            'type'  => 'array',
                            'el'    => &$sectionEl,
                        ],
                    ],
                ];

                $pointer[substr($child->getId(), 1)] = $section;
                $previousPointer                     = $pointer;
                $pointer                             = &$sectionEl;
                continue;
            }

            if ($child === null) {
                $pointer         = &$previousPointer;
                $previousPointer = null;
                continue;
            }

            if ($child instanceof FlexField) {
                $pointer[$child->getId()] = ['TCEforms' => $child->getRaw()];
            }
        }

        return $result;
    }

    /**
     * Dumps the given flex form object back into an xml string
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Flex  $flex
     *
     * @return string
     */
    public function dumpAsString(Flex $flex): string
    {
        $array = $this->dumpAsArray($flex);

        return $this->tools->flexArray2Xml($array);
    }

    /**
     * Dumps the given flex form into a file on the disk and returns the absolute filepath to its location.
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Flex  $flex
     *
     * @return string
     */
    public function dumpToFile(Flex $flex): string
    {
        $content  = $this->dumpAsString($flex);
        $filename = 'flexForm-' . md5($content) . '.xml';
        $this->fs->setFileContent($filename, $content);

        return $this->fs->getFile($filename)->getPathname();
    }
}
