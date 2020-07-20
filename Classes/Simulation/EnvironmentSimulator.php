<?php
declare(strict_types=1);
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
 * Last modified: 2020.03.20 at 13:59
 */

namespace LaborDigital\Typo3BetterApi\Simulation;

use LaborDigital\Typo3BetterApi\Container\CommonDependencyTrait;
use LaborDigital\Typo3BetterApi\Simulation\Pass\AdminSimulationPass;
use LaborDigital\Typo3BetterApi\Simulation\Pass\LanguageSimulationPass;
use LaborDigital\Typo3BetterApi\Simulation\Pass\SimulatorPassInterface;
use LaborDigital\Typo3BetterApi\Simulation\Pass\SiteSimulationPass;
use LaborDigital\Typo3BetterApi\Simulation\Pass\TsfeSimulationPass;
use LaborDigital\Typo3BetterApi\Simulation\Pass\VisibilitySimulationPass;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class EnvironmentSimulator
 *
 * This class is highly experimental, there may be bugs on your way!
 *
 * @package LaborDigital\Typo3BetterApi\Simulation
 */
class EnvironmentSimulator implements SingletonInterface
{
    use CommonDependencyTrait;
    
    /**
     * The list of simulation pass classes with the option of extension.
     * All classes registered MUST implement the SimulatorPassInterface!
     * The order of these classes is important!
     *
     * @var array
     *
     * @see SimulatorPassInterface
     */
    public static $environmentSimulatorPasses
        = [
            SiteSimulationPass::class,
            AdminSimulationPass::class,
            VisibilitySimulationPass::class,
            LanguageSimulationPass::class,
            TsfeSimulationPass::class,
        ];
    
    /**
     * This is true when we are currently running inside a transformation
     *
     * @var bool
     */
    protected $isInSimulation = false;
    
    /**
     * True if child simulations should be ignored
     *
     * @var bool
     */
    protected $childSimulationsIgnored = false;
    
    /**
     * Can be used to run a function in a different environment.
     * You can use this method to render frontend content's in the backend or in the CLI,
     * but you can also use it in the frontend to change the context PID / the language to something different.
     *
     * @param   array     $options  The options to define the new environment
     *                              - pid int: Can be used to change the page id of the executed process.
     *                              If this is left empty the current page id is used
     *                              - language int|string|SiteLanguage: The language to set the environment to.
     *                              Either as sys_language_uid value, as iso code or as language object
     *                              - fallbackLanguage int|string|SiteLanguage: The language which should be used when
     *                              the $language was not found for this site.
     *                              - site string: Can be set to a valid site identifier to simulate the request
     *                              on a specific TYPO3 site.
     *                              - bootTsfe bool (TRUE): By default the simulator will start a dummy version of
     *                              the frontend controller and populate the $GLOBALS["TSFE"] variable with it.
     *                              If you set this to false, the TSFE is not booted; it may already exist tho!
     *                              - ignoreChildSimulations bool (FALSE): If this is set to true nested calls
     *                              of this simulator will be skipped and the handler will be executed in the
     *                              environment of the parent simulator
     *                              - ignoreInSimulations bool (FALSE): If this is set to TRUE the simulation
     *                              will not be done when the script is currently running inside another simulation.
     *                              - ignoreIfFrontendExists bool (FALSE): If this is set to true there will be no
     *                              simulation if we detect an already initialized TSFE. Useful to let frontend
     *                              code run in the backend without adding overhead to the frontend
     *                              - includeHiddenPages bool (FALSE): If this is set to true the closure will
     *                              have access to all hidden pages.
     *                              - includeHiddenContent bool (FALSE): If this is set to true the closure will
     *                              have access to all hidden content elements on when retrieving tt_content data
     *                              - includeDeletedRecords bool (FALSE): If this is set to true the requests
     *                              made in the closure will include deleted records
     *                              - asAdmin bool (FALSE): Read below in the "asAdmin" section
     *
     *
     * A word on the "asAdmin" option:
     * =========================================================================
     * ATTENTION: This method is extremely powerful and you should really consider twice if you want to use it
     * for whatever you want to achieve.
     *
     * What it does: This method allows execution of the given $callback as an administrator user!
     * This will allow you to circumvent all permissions, access rights and user groups that are configured in the
     * backend. There are no safeguards here! It's like writing data directly into the database; If you don't know what
     * you do inside the scope of this method's callback you can break a lot of stuff...
     *
     * This works in the Frontend, in the Backend in the CLI and wherever else.
     *
     * It will create a new backend user called _betterApi_adminUser_ for you which is used as user object
     * inside the closure. Every action performed, and logged will be executed as this user; except you are
     * already logged in as an administrator, in that case we just use your account!
     *
     * So again: Use it, but PLEASE use it with care!
     *
     * @param   callable  $handler  The callback to execute inside the modified context
     *
     * @return mixed|null
     */
    public function runWithEnvironment(array $options, callable $handler)
    {
        // Ignore the simulation if required
        $earlyOptions = Options::make($options, $this->getDefaultOptionDefinition(), ['ignoreUnknown' => true]);
        if (
            ($this->isInSimulation && $earlyOptions['ignoreInSimulations'])
            || ($this->isInSimulation && $this->childSimulationsIgnored)
            || ($earlyOptions['ignoreIfFrontendExists'] && $this->Tsfe()->hasTsfe())
        ) {
            return $handler();
        }
        
        // Apply the real options
        $passes  = $this->makePassInstances();
        $options = Options::make($options, $this->getSimulatorOptions($passes));
        
        // Backup the old ignore child simulation state
        $parentIgnoresChildSimulations = $this->childSimulationsIgnored;
        $this->childSimulationsIgnored = $options['ignoreChildSimulations'];
        $parentIsInSimulation          = $this->isInSimulation;
        
        // Set up the simulation
        $rollBackPasses = [];
        $result         = null;
        try {
            foreach ($passes as $pass) {
                if ($pass->requireSimulation($options)) {
                    $rollBackPasses[] = $pass;
                    $pass->setup($options);
                }
            }
            
            // Update the simulation state
            $this->isInSimulation = $this->isInSimulation || ! empty($rollBackPasses);
            
            // Run the handler
            $result = $handler();
            
        } finally {
            // Roll back
            foreach (array_reverse($rollBackPasses) as $pass) {
                $pass->rollBack();
            }
            
            // Restore parent state
            $this->childSimulationsIgnored = $parentIgnoresChildSimulations;
            $this->isInSimulation          = $parentIsInSimulation;
        }
        
        // Done
        return $result;
    }
    
    /**
     * ATTENTION: This method is extremely powerful and you should really consider twice if you want to use it
     * for whatever you want to achieve.
     *
     * What it does: This method allows execution of the given $callback as an administrator user!
     * This will allow you to circumvent all permissions, access rights and user groups that are configured in the
     * backend. There are no safeguards here! It's like writing data directly into the database; If you don't know what
     * you do inside the scope of this method's callback you can break a lot of stuff...
     *
     * This works in the Frontend, in the Backend in the CLI and wherever else.
     *
     * It will create a new backend user called _betterApi_adminUser_ for you which is used as user object
     * inside the closure. Every action performed, and logged will be executed as this user; except you are
     * already logged in as an administrator, in that case we just use your account!
     *
     * So again. Use it, but please use it with care!
     *
     * @param   callable  $handler  A callback to be executed in the context of an administrator
     *
     * @return mixed
     * @deprecated will be removed in v10 use runWithEnvironment(['asAdmin'], function(){}) instead!
     */
    public function runAsAdmin(callable $handler)
    {
        return $this->runWithEnvironment(['asAdmin'], $handler);
    }
    
    /**
     * Creates the instances of the simulator passes
     *
     * @return \LaborDigital\Typo3BetterApi\Simulation\Pass\SimulatorPassInterface[]
     */
    protected function makePassInstances(): array
    {
        $instances = [];
        foreach (static::$environmentSimulatorPasses as $passClass) {
            $instance = GeneralUtility::makeInstance($passClass);
            if (! $instance instanceof SimulatorPassInterface) {
                continue;
            }
            $instances[] = $instance;
        }
        
        return $instances;
    }
    
    /**
     * Collects the simulator options from the simulator passes
     *
     * @param   SimulatorPassInterface[]  $passes  The list of passes to extract the definition from
     *
     * @return array
     */
    protected function getSimulatorOptions(array $passes): array
    {
        $definition = $this->getDefaultOptionDefinition();
        foreach ($passes as $pass) {
            $definition = $pass->addOptionDefinition($definition);
        }
        
        return $definition;
    }
    
    /**
     * Returns the default option definition
     *
     * @return array
     */
    protected function getDefaultOptionDefinition(): array
    {
        return [
            'ignoreChildSimulations' => [
                'type'    => 'bool',
                'default' => $this->childSimulationsIgnored,
            ],
            'ignoreInSimulations'    => [
                'type'    => 'bool',
                'default' => false,
            ],
            'ignoreIfFrontendExists' => [
                'type'    => 'bool',
                'default' => false,
            ],
        ];
    }
}
