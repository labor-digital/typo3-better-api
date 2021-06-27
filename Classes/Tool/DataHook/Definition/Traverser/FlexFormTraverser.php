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


namespace LaborDigital\T3ba\Tool\DataHook\Definition\Traverser;


use LaborDigital\T3ba\Tool\DataHook\Definition\DataHookDefinition;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Flex;
use Neunerlei\Arrays\Arrays;
use Throwable;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;

class FlexFormTraverser extends AbstractTraverser
{
    /**
     * @var \TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools
     */
    protected $tools;
    
    /**
     * The root path to the actual flex form field
     *
     * @var array
     */
    protected $rootPath;
    
    /**
     * The relevant data to traverse
     *
     * @var array
     */
    protected $data;
    
    public function __construct(FlexFormTools $tools)
    {
        $this->tools = $tools;
    }
    
    /**
     * @inheritDoc
     */
    public function initialize(DataHookDefinition $definition, array $rootPath = []): AbstractTraverser
    {
        $this->rootPath = $rootPath;
        $this->data = Arrays::getPath($definition->data, $rootPath, []);
        
        return parent::initialize($definition);
    }
    
    /**
     * Iterates the flex form data structure to find registered data hook handlers to process
     */
    public function traverse(): void
    {
        // Ignore empty data
        if (empty($this->data)) {
            return;
        }
        
        // Extract the structure for the flex form to process
        try {
            $fieldName = reset($this->rootPath);
            $fieldTca = $this->definition->tca['columns'][$fieldName];
            $structureId = $this->tools->getDataStructureIdentifier(
                $fieldTca,
                $this->definition->tableName,
                $fieldName,
                $this->definition->data
            );
            $structure = $this->tools->parseDataStructureByIdentifier($structureId);
        } catch (Throwable $e) {
            return;
        }
        
        // Ignore if we could not find a structure
        if (empty($structure)) {
            return;
        }
        
        $this->traverseStructure($structure);
    }
    
    /**
     * Traverses the sheets inside the given structure and register data hooks for them
     *
     * @param   array  $structure
     */
    protected function traverseStructure(array $structure): void
    {
        if (empty($structure['sheets']) || ! is_array($structure['sheets'])) {
            return;
        }
        
        foreach ($structure['sheets'] as $key => $sheet) {
            $path = [$key];
            $this->findHooksRecursively($sheet, $path);
        }
    }
    
    /**
     * Digs deep into the given structure array, by recursively traversing all of it's children.
     * It will register the handlers for elements and even for elements inside of sections.
     *
     * NOTE: formHooks for elements inside of sections only work if they have data in the section.
     *
     * @param   array  $structure
     * @param   array  $path
     */
    protected function findHooksRecursively(array $structure, array $path): void
    {
        foreach ($structure as $k => $v) {
            if ($k === 'TCEforms' && isset($v['config'])) {
                // This field is inside a section -> treat those with special care...
                if (count($path) > 4) {
                    $sectionDataPath = $this->translateHookPathToSectionDataPath($path);
                    $sectionData = Arrays::getPath($this->data, $sectionDataPath, []);
                    $sectionEls = array_keys($sectionData);
                    $childDataPath = $this->translateHookPathToSectionChildDataPath($path);
                    
                    foreach ($sectionEls as $el) {
                        $elDataPath = array_merge($this->rootPath, $sectionDataPath, [$el], $childDataPath);
                        $this->registerHandlerDefinitions(
                            $this->translateHookPathToFieldKey($path),
                            $v,
                            $elDataPath
                        );
                    }
                    
                    continue;
                }
                
                // Register a handler for a hook field
                $this->registerHandlerDefinitions(
                    $this->translateHookPathToFieldKey($path),
                    $v,
                    $this->translateHookPathToDataPath($path)
                );
                continue;
            }
            
            if (is_array($v)) {
                $this->findHooksRecursively($v, array_merge($path, [$k]));
            }
        }
    }
    
    /**
     * Internal helper to translate a hook path to the data path of the children inside a section.
     *
     * @param   array  $hookPath
     *
     * @return array
     */
    protected function translateHookPathToSectionDataPath(array $hookPath): array
    {
        $path = $this->translateHookPathToDataPath(array_slice($hookPath, 0, 4));
        $path = array_slice($path, 1, -1);
        $path[] = 'el';
        
        return $path;
    }
    
    /**
     * Internal helper that translates the hook path to the data sub-path of a single child inside a section.
     * The resulting path is relative to the sectionDataPath generated with translateHookPathToSectionDataPath()
     *
     * @param   array  $hookPath
     *
     * @return array
     */
    protected function translateHookPathToSectionChildDataPath(array $hookPath): array
    {
        $path = $this->translateHookPathToDataPath(array_slice($hookPath, 4), true);
        
        return array_slice($path, 3);
    }
    
    /**
     * Internal helper that translates the path to the hook definition to the actual field definition
     *
     * @param   array  $hookPath
     * @param   bool   $keepEl
     *
     * @return string[]
     */
    protected function translateHookPathToDataPath(array $hookPath, bool $keepEl = false): array
    {
        $path = $this->rootPath;
        $path[] = 'data';
        
        foreach ($hookPath as $k) {
            if (! $keepEl && $k === 'el') {
                continue;
            }
            
            if ($k === 'ROOT') {
                $k = 'lDEF';
            }
            
            $path[] = $k;
        }
        
        $path[] = 'vDEF';
        
        return $path;
    }
    
    /**
     * Internal helper to translate the hook path into the unique field key
     *
     * @param   array  $hookPath
     *
     * @return string
     */
    protected function translateHookPathToFieldKey(array $hookPath): string
    {
        $tab = reset($hookPath);
        $field = end($hookPath);
        
        return $tab . Flex::PATH_SEPARATOR . $field;
    }
}
