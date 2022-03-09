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
 * Last modified: 2022.03.09 at 14:13
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Page\Util\Content;

use LaborDigital\T3ba\Event\PageContentsGridConfigFilterEvent;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use Neunerlei\Arrays\Arrays;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Internal utility class which is used to sort a list of tt_content records by their sorting,
 * by taking all possible grid providers (flux,gridelements,ect...) into account.
 */
class ContentSorter
{
    protected const GRID_PROVIDERS
        = [
            [
                'parentField' => 'tx_gridelements_container',
                'parentColField' => 'tx_gridelements_columns',
            ],
        ];
    /**
     * @var \Psr\EventDispatcher\EventDispatcherInterface
     */
    protected EventDispatcherInterface $eventDispatcher;
    
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
    
    /**
     * Performs the sorting of the given records
     *
     * @param   int    $pageId   The uid of the page the tt_content records are part of
     * @param   array  $records  A list of raw tt_content rows to be sorted
     *
     * @return array
     */
    public function sort(int $pageId, array $records): array
    {
        $e = $this->eventDispatcher->dispatch(new PageContentsGridConfigFilterEvent($pageId, $records, static::GRID_PROVIDERS));
        
        // The Serialization makes sure that there are no references left.
        return SerializerUtil::unserializeJson(
            SerializerUtil::serializeJson(
                $this->runOutputGenerationPass(
                    $this->runElementNestingPass(
                        $this->runElementCreationPass($e->getRecords()),
                        $e->getCustomGrids()
                    )
                )
            )
        );
    }
    
    /**
     * Receives the list of records and creates a meta information provider out of it
     *
     * @param   array  $records
     *
     * @return array
     * @todo should we use objects here instead?
     */
    protected function runElementCreationPass(array $records): array
    {
        $elements = [];
        foreach ($records as $record) {
            $uid = $record['uid'];
            $row = [
                'parent' => null,
                'colPos' => $record['colPos'] ?? 0,
                'uid' => $uid,
                'record' => $record,
                'children' => [],
                'sorting' => $record['sorting'] ?? 0,
            ];
            $elements[$uid] = $row;
        }
        
        return $elements;
    }
    
    /**
     * Receives the list of elements prepared by runElementCreationPass and nests them into each other
     * as defined by the available grid providers.
     *
     * @param   array  $elements
     * @param   array  $customGrids
     *
     * @return array
     */
    protected function runElementNestingPass(array $elements, array $customGrids): array
    {
        foreach ($elements as &$element) {
            $parent = null;
            $record = $element['record'];
            $colPos = $record['colPos'];
            
            foreach ($customGrids as $customGridConfig) {
                // Ignore if the custom grid has no parent field -> misconfiguration
                if (! isset($customGridConfig['parentField'])) {
                    continue;
                }
                // Ignore if the records does not have the required parent field
                if (empty($record[$customGridConfig['parentField']])) {
                    continue;
                }
                // Map The parent
                $parent = $record[$customGridConfig['parentField']];
                $colPos = 0;
                // Check if the parent col field exists
                if (isset($customGridConfig['parentColField'])
                    && ! empty($record[$customGridConfig['parentColField']])) {
                    $colPos = $record[$customGridConfig['parentColField']];
                }
                break;
            }
            
            // Check if we can map the record as a child
            if (empty($parent)) {
                continue;
            }
            
            // Strip out element's that define a parent which is not in our element list -> broken relation?
            if (! isset($elements[$parent])) {
                $element['parent'] = false;
                continue;
            }
            
            // Map the element into a tree
            $element['parent'] = $parent;
            $element['colPos'] = $colPos;
            $elements[$parent]['children'][$colPos][$element['uid']] = &$element;
        }
        
        return $elements;
    }
    
    /**
     * Iterates the prepared and already nested elements and creates a new, clean output array out of them
     *
     * @param   array  $elements
     *
     * @return array
     */
    protected function runOutputGenerationPass(array $elements): array
    {
        $output = [];
        foreach ($elements as &$element) {
            // Sort the element in the child array
            if (! empty($element['children'])) {
                foreach ($element['children'] as &$childCol) {
                    $childCol = Arrays::sortBy($childCol, 'sorting');
                }
                unset($childCol);
            }
            
            // Build the output
            if ($element['parent'] === null) {
                $output[$element['colPos']][$element['uid']] = $element;
            }
        }
        unset($element);
        
        // Sort the elements inside the cols
        foreach ($output as &$col) {
            $col = Arrays::sortBy($col, 'sorting');
        }
        
        return $output;
    }
}