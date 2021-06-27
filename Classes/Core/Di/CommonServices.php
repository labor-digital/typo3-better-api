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


namespace LaborDigital\T3ba\Core\Di;


use Closure;
use LaborDigital\T3ba\Core\VarFs\VarFs;
use LaborDigital\T3ba\Tool\Database\DbService;
use LaborDigital\T3ba\Tool\DataHandler\DataHandlerService;
use LaborDigital\T3ba\Tool\Fal\FalService;
use LaborDigital\T3ba\Tool\Link\LinkService;
use LaborDigital\T3ba\Tool\Page\PageService;
use LaborDigital\T3ba\Tool\Session\SessionService;
use LaborDigital\T3ba\Tool\Simulation\EnvironmentSimulator;
use LaborDigital\T3ba\Tool\Translation\Translator;
use LaborDigital\T3ba\Tool\Tsfe\TsfeService;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use LaborDigital\T3ba\Tool\TypoScript\TypoScriptService;
use LaborDigital\T3ba\TypoContext\DependencyInjectionFacet;
use Neunerlei\EventBus\EventBusInterface;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\DependencyInjection\NotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class CommonServices
 *
 * A simple implementation of a common service "locator".
 * While not really a "best-practice" approach to programming, those services are needed
 * quite often and only in specific cases, where it does not make any sense to inject them.
 *
 * Will I burn in hell for this? Maybe, maybe not...
 *
 * As a general note: -> Try to keep your code free from hidden dependencies
 * ALWAYS ASK YOURSELF: Does it really make sense to use this?
 *
 * The $generator is used to allow overrides in a testing context and to utilize the internal
 * singleton storage when used in a ContainerAwareTrait
 *
 * @package LaborDigital\T3ba\Core\DependencyInjection
 *
 * @see     \LaborDigital\T3ba\Core\Di\ContainerAwareTrait
 * @see     \LaborDigital\T3ba\Core\Di\StaticContainerAwareTrait
 *
 * @property DbService                $db
 * @property LinkService              $links
 * @property TsfeService              $tsfe
 * @property PageService              $page
 * @property FalService               $fal
 * @property EventBusInterface        $eventBus
 * @property TypoScriptService        $typoScript
 * @property TypoScriptService        $ts
 * @property Translator               $translator
 * @property EnvironmentSimulator     $simulator
 * @property SessionService           $session
 * @property DataHandlerService       $dataHandler
 * @property ObjectManager            $objectManager
 * @property TypoContext              $typoContext
 * @property DependencyInjectionFacet $di
 * @property ContainerInterface       $container
 * @property VarFs                    $varFs
 */
class CommonServices implements PublicServiceInterface
{
    /**
     * Defines how the specific properties should be resolved.
     *
     * Arguments:
     * 0: Name of the class to resolve
     * 1: True if a new instance of the class should be returned, false/null if a singleton can be used
     *
     * @var array
     */
    protected $def
        = [
            'db' => DbService::class,
            'links' => LinkService::class,
            'tsfe' => TsfeService::class,
            'page' => PageService::class,
            'fal' => FalService::class,
            'eventBus' => EventBusInterface::class,
            'typoScript' => TypoScriptService::class,
            'ts' => TypoScriptService::class,
            'varFs' => VarFs::class,
            'translator' => Translator::class,
            'simulator' => EnvironmentSimulator::class,
            'session' => SessionService::class,
            'dataHandler' => DataHandlerService::class,
            'typoContext' => TypoContext::class,
        ];
    
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;
    
    /**
     * CommonServices constructor.
     *
     * @param   \Psr\Container\ContainerInterface  $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Magic method to resolve the required instance based on the property
     *
     * @param $name
     *
     * @return mixed
     * @throws \TYPO3\CMS\Core\DependencyInjection\NotFoundException
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($name)
    {
        if (! isset($this->def[$name])) {
            switch ($name) {
                case 'container':
                    return $this->container;
                case 'di':
                    return $this->typoContext->di();
                case 'objectManager':
                    return GeneralUtility::makeInstance(ObjectManager::class);
            }
            
            throw new NotFoundException('Invalid common service "' . $name . '" requested!');
        }
        
        if (is_object($this->def[$name])) {
            // Use a factory
            if ($this->def[$name] instanceof Closure) {
                return $this->def[$name]($this->container, $name);
            }
            
            // Return the instance
            return $this->def[$name];
        }
        
        return $this->container->get($this->def[$name]);
    }
    
}
