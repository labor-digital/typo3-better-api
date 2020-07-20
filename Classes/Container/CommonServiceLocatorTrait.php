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
 * Last modified: 2020.03.21 at 15:58
 */

namespace LaborDigital\Typo3BetterApi\Container;

use Closure;
use LaborDigital\Typo3BetterApi\Cache\FrontendCache;
use LaborDigital\Typo3BetterApi\Cache\GeneralCache;
use LaborDigital\Typo3BetterApi\Cache\PageBasedCache;
use LaborDigital\Typo3BetterApi\Domain\DbService\DbServiceInterface;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;
use LaborDigital\Typo3BetterApi\Link\LinkService;
use LaborDigital\Typo3BetterApi\Page\PageService;
use LaborDigital\Typo3BetterApi\Rendering\BackendRenderingService;
use LaborDigital\Typo3BetterApi\Rendering\FlashMessageRenderingService;
use LaborDigital\Typo3BetterApi\Rendering\TemplateRenderingService;
use LaborDigital\Typo3BetterApi\Session\SessionService;
use LaborDigital\Typo3BetterApi\Simulation\EnvironmentSimulator;
use LaborDigital\Typo3BetterApi\Translation\TranslationService;
use LaborDigital\Typo3BetterApi\Tsfe\TsfeService;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use LaborDigital\Typo3BetterApi\TypoScript\TypoScriptService;
use Neunerlei\EventBus\EventBusInterface;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Service\FlexFormService;

define('COMMON_SERVICE_LOCATOR_STATIC_SERVICE_MAP', [
    'Translation'           => TranslationService::class,
    'Session'               => SessionService::class,
    'TypoScript'            => TypoScriptService::class,
    'Db'                    => DbServiceInterface::class,
    'FalFiles'              => FalFileService::class,
    'EventBus'              => EventBusInterface::class,
    'Links'                 => LinkService::class,
    'Tsfe'                  => TsfeService::class,
    'FlexForm'              => FlexFormService::class,
    'BackendRendering'      => BackendRenderingService::class,
    'TemplateRendering'     => TemplateRenderingService::class,
    'FlashMessageRendering' => FlashMessageRenderingService::class,
    'Page'                  => PageService::class,
    'PageBasedCache'        => PageBasedCache::class,
    'GeneralCache'          => GeneralCache::class,
    'FrontendCache'         => FrontendCache::class,
    'DataHandler'           => DataHandler::class,
    'Container'             => TypoContainerInterface::class,
    'TypoContext'           => TypoContext::class,
    'Simulator'             => EnvironmentSimulator::class,
]);

trait CommonServiceLocatorDeprecationTrait
{
    /**
     * @var TranslationService
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $Translation;
    
    /**
     * @var SessionService
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $Session;
    
    /**
     * @var TypoScriptService
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $TypoScript;
    
    /**
     * @var DbServiceInterface
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $Db;
    
    /**
     * @var FalFileService
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $FalFiles;
    
    /**
     * @var EventBusInterface
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $EventBus;
    
    /**
     * @var LinkService
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $Links;
    
    /**
     * @var TsfeService
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $Tsfe;
    
    /**
     * @var EnvironmentSimulator
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $Simulator;
    
    /**
     * @var FlexFormService
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $FlexForm;
    
    /**
     * @var BackendRenderingService
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $BackendRendering;
    
    /**
     * @var TemplateRenderingService
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $TemplateRendering;
    
    /**
     * @var FlashMessageRenderingService
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $FlashMessageRendering;
    
    /**
     * @var PageService
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $Page;
    
    /**
     * @var PageBasedCache
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $PageBasedCache;
    
    /**
     * @var FrontendCache
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $FrontendCache;
    
    /**
     * @var GeneralCache
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $GeneralCache;
    
    /**
     * @var TypoContainerInterface
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $Container;
    
    /**
     * @var DataHandler
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $DataHandler;
    
    /**
     * @var TypoContext
     * @deprecated Will be removed in v10 - Use ContainerAwareTrait or LazySingletonTrait instead
     */
    public $TypoContext;
}

/**
 * Trait CommonServiceLocatorTrait
 *
 * @mixin CommonServiceLocatorDeprecationTrait
 * @package    LaborDigital\Typo3BetterApi\Container
 *
 * @deprecated Will be removed in v10 - Use ContainerAwareTrait or CommonDependencyTrait instead
 * @see        \LaborDigital\Typo3BetterApi\Container\ContainerAwareTrait
 * @see        \LaborDigital\Typo3BetterApi\Container\CommonDependencyTrait
 */
trait CommonServiceLocatorTrait
{
    
    /**
     * The instance of the container we will use as service locator
     *
     * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
     */
    protected $__container;
    
    /**
     * The local instance cache to avoid asking the container over and over again...
     *
     * @var array
     */
    protected $__instances = [];
    
    /**
     * The map of the variable name to the matching service class
     *
     * @var array
     */
    protected $__serviceMap = [];
    
    /**
     * True if the trait was initialized and the static mapping was loaded
     *
     * @var bool
     */
    protected $__traitInitialized = false;
    
    /**
     * Injects the container instance we use as service locator
     *
     * @param   \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface  $container
     */
    public function injectContainer(TypoContainerInterface $container): void
    {
        $this->__container = $container;
    }
    
    /**
     * You can use this method if you want to lazy load an object using the container instance.
     *
     * Note: You should try to avoid this method as hard as possible!
     * This is the opposite of IoC and how you should use dependency injection.
     * However: There are some good examples of where you might want to use it:
     * Inside Models, or callbacks that don't support dependency injection for example.
     *
     * @param   string  $class  The class or interface you want to retrieve the object for
     * @param   array   $args   Optional, additional constructor arguments
     *
     * @return mixed
     * @deprecated Will be removed in v10
     */
    public function getInstanceOf(string $class, array $args = [])
    {
        if (! $this->__traitInitialized) {
            $this->__initializeTrait();
        }
        if (empty($this->__container)) {
            $this->__container = TypoContainer::getInstance();
        }
        
        return $this->__container->get($class, ['args' => $args]);
    }
    
    /**
     * Should be called in the __construct method of the including class.
     * Can be used to add additional service classes to the list of resolvable properties
     *
     * @param   array  $map  A list of "PropertyName" => My\Class\Name to define additional services
     *                       If the instance of an object is passed as value in the mapping array it will be used as
     *                       instance for that property
     *
     * @deprecated Will be removed in v10
     */
    public function addToServiceMap(array $map)
    {
        if (! $this->__traitInitialized) {
            $this->__initializeTrait();
        }
        $this->__serviceMap = array_merge($this->__serviceMap, $map);
    }
    
    /**
     * Magic method to handle the lazy lookup
     *
     * @param $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        // Initialize if required
        if (! $this->__traitInitialized) {
            $this->__initializeTrait();
        }
        
        // Return existing instances
        if (! empty($this->__instances[$name])) {
            return $this->__instances[$name];
        }
        
        // Try to fix broken names
        if (! isset($this->__serviceMap[$name])) {
            $name = Inflector::toCamelCase($name);
        }
        
        // Ignore not registered services
        if (! isset($this->__serviceMap[$name])) {
            return null;
        }
        
        // Return existing instances with fixed names...
        if (! empty($this->__instances[$name])) {
            return $this->__instances[$name];
        }
        
        // Get the service definition
        $definition = $this->__serviceMap[$name];
        
        // Handle objects
        if (is_object($definition)) {
            // Check if we got a factory closure
            if ($definition instanceof Closure) {
                return $this->__instances[$name] = call_user_func($definition, $this->Container);
            }
            
            // Use the definition as instance
            return $this->__instances[$name] = $definition;
        }
        
        // Create a new object
        return $this->__instances[$name] = $this->getInstanceOf($definition);
    }
    
    /**
     * Loads an optional static mapping from the $this->serviceMap from the parent class
     */
    private function __initializeTrait()
    {
        if ($this->__traitInitialized) {
            return;
        }
        $this->__traitInitialized = true;
        
        // Load the global, static service map
        $this->__serviceMap = COMMON_SERVICE_LOCATOR_STATIC_SERVICE_MAP;
        
        // Check if this object defines a static service map
        if (isset($this->serviceMap) && is_array($this->serviceMap)) {
            $this->addToServiceMap($this->serviceMap);
        }
    }
}
