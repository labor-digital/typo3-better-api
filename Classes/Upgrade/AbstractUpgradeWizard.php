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
 * Last modified: 2021.07.19 at 14:54
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Upgrade;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\ExtConfigHandler\UpgradeWizard\ConfigureUpgradeWizardInterface;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Install\Updates\ChattyInterface;

abstract class AbstractUpgradeWizard implements ConfigureUpgradeWizardInterface, ChattyInterface
{
    use ContainerAwareTrait;
    
    /**
     * @var OutputInterface
     */
    protected $output;
    
    /**
     * @inheritDoc
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }
    
    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        $wizards = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update'];
        $key = array_search(static::class, $wizards, true);
        
        if (! $key) {
            $ns = explode('\\', static::class);
            $key = lcfirst($ns[1] ?? '');
            
            $key .= '_' . lcfirst(Path::classBasename(static::class));
        }
        
        return $key;
    }
    
    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return Inflector::toHuman(preg_replace('~_~', ': ', $this->getIdentifier(), 1));
    }
    
    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return '';
    }
    
    /**
     * @inheritDoc
     */
    public function updateNecessary(): bool
    {
        return true;
    }
    
    /**
     * @inheritDoc
     */
    public function getPrerequisites(): array
    {
        return [];
    }
    
}