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
 * Last modified: 2021.07.26 at 14:38
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Simulation\Pass;


use LaborDigital\T3ba\Tool\Simulation\Adapter\PageRepositoryAdapter;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;

class VisibilitySimulationPass implements SimulatorPassInterface
{
    use TypoContextAwareTrait;
    
    /**
     * @inheritDoc
     */
    public function addOptionDefinition(array $options): array
    {
        $options['includeHiddenPages'] = [
            'type' => ['bool', 'null'],
            'default' => null,
        ];
        $options['includeHiddenContent'] = [
            'type' => ['bool', 'null'],
            'default' => null,
        ];
        $options['includeDeletedRecords'] = [
            'type' => ['bool', 'null'],
            'default' => null,
        ];
        
        return $options;
    }
    
    /**
     * @inheritDoc
     */
    public function requireSimulation(array $options, array &$storage): bool
    {
        $visibilityAspect = $this->getTypoContext()->visibility();
        
        return
            (is_bool($options['includeHiddenPages']) && $options['includeHiddenPages'] !== $visibilityAspect->includeHiddenPages())
            || (is_bool($options['includeHiddenContent']) && $options['includeHiddenContent'] !== $visibilityAspect->includeHiddenContent())
            || (is_bool($options['includeDeletedRecords']) && $options['includeDeletedRecords'] !== $visibilityAspect->includeDeletedRecords());
    }
    
    /**
     * @inheritDoc
     */
    public function setup(array $options, array &$storage): void
    {
        // Backup the aspect
        $storage['aspect'] = clone $this->getTypoContext()->getRootContext()->getAspect('visibility');
        
        // Update the aspect
        $visibilityAspect = $this->getTypoContext()->visibility();
        if (is_bool($options['includeHiddenPages'])) {
            $visibilityAspect->setIncludeHiddenPages($options['includeHiddenPages']);
        }
        if (is_bool($options['includeHiddenContent'])) {
            $visibilityAspect->setIncludeHiddenContent($options['includeHiddenContent']);
        }
        if (is_bool($options['includeDeletedRecords'])) {
            $visibilityAspect->setIncludeDeletedRecords($options['includeDeletedRecords']);
        }
        
        // Update page repository
        if (isset($GLOBALS['TSFE']->sys_page)) {
            $storage['pageRepoAccess'] = PageRepositoryAdapter::reinitializeWithState(
                $GLOBALS['TSFE']->sys_page, $options['includeHiddenPages']
            );
        }
    }
    
    /**
     * @inheritDoc
     */
    public function rollBack(array $storage): void
    {
        $this->getTypoContext()->getRootContext()->setAspect('visibility', $storage['aspect']);
        
        if (isset($storage['pageRepoAccess']) && isset($GLOBALS['TSFE']->sys_page)) {
            PageRepositoryAdapter::restoreAccessRules($GLOBALS['TSFE']->sys_page, $storage['pageRepoAccess']);
        }
    }
    
}
