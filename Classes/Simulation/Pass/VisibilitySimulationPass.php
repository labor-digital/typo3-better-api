<?php
/**
 * Copyright 2020 LABOR.digital
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
 * Last modified: 2020.07.16 at 21:17
 */

declare(strict_types=1);


namespace LaborDigital\Typo3BetterApi\Simulation\Pass;


use LaborDigital\Typo3BetterApi\Container\CommonDependencyTrait;

class VisibilitySimulationPass implements SimulatorPassInterface
{
    use CommonDependencyTrait;
    
    protected $aspectBackup;
    
    /**
     * @inheritDoc
     */
    public function __construct() { }
    
    /**
     * @inheritDoc
     */
    public function addOptionDefinition(array $options): array
    {
        $options['includeHiddenPages']    = [
            'type'    => 'bool',
            'default' => false,
        ];
        $options['includeHiddenContent']  = [
            'type'    => 'bool',
            'default' => false,
        ];
        $options['includeDeletedRecords'] = [
            'type'    => 'bool',
            'default' => false,
        ];
        
        return $options;
        
    }
    
    /**
     * @inheritDoc
     */
    public function requireSimulation(array $options): bool
    {
        $visibilityAspect = $this->TypoContext()->Visibility();
        
        return $options['includeHiddenPages'] !== $visibilityAspect->includeHiddenPages()
               || $options['includeHiddenContent'] !== $visibilityAspect->includeHiddenContent()
               || $options['includeDeletedRecords'] !== $visibilityAspect->includeDeletedRecords();
    }
    
    /**
     * @inheritDoc
     */
    public function setup(array $options): void
    {
        // Backup the aspect
        $this->aspectBackup = clone $this->TypoContext()->getRootContext()->getAspect('visibility');
        
        // Update the aspect
        $visibilityAspect = $this->TypoContext()->Visibility();
        $visibilityAspect->setIncludeHiddenPages($options['includeHiddenPages']);
        $visibilityAspect->setIncludeHiddenContent($options['includeHiddenContent']);
        $visibilityAspect->setIncludeDeletedRecords($options['includeDeletedRecords']);
    }
    
    /**
     * @inheritDoc
     */
    public function rollBack(): void
    {
        $this->TypoContext()->getRootContext()->setAspect('visibility', $this->aspectBackup);
    }
    
}