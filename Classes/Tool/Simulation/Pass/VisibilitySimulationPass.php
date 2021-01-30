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


namespace LaborDigital\T3BA\Tool\Simulation\Pass;


use LaborDigital\T3BA\Tool\TypoContext\TypoContextAwareTrait;

class VisibilitySimulationPass implements SimulatorPassInterface
{
    use TypoContextAwareTrait;

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
    public function requireSimulation(array $options, array &$storage): bool
    {
        $visibilityAspect = $this->getTypoContext()->visibility();

        return $options['includeHiddenPages'] !== $visibilityAspect->includeHiddenPages()
               || $options['includeHiddenContent'] !== $visibilityAspect->includeHiddenContent()
               || $options['includeDeletedRecords'] !== $visibilityAspect->includeDeletedRecords();
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
        $visibilityAspect->setIncludeHiddenPages($options['includeHiddenPages']);
        $visibilityAspect->setIncludeHiddenContent($options['includeHiddenContent']);
        $visibilityAspect->setIncludeDeletedRecords($options['includeDeletedRecords']);
    }

    /**
     * @inheritDoc
     */
    public function rollBack(array $storage): void
    {
        $this->getTypoContext()->getRootContext()->setAspect('visibility', $storage['aspect']);
    }

}
