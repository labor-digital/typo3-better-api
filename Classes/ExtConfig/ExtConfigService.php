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
 * Last modified: 2020.03.21 at 16:01
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig;

use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\Event\Events\ExtConfigBeforeLoadEvent;
use LaborDigital\Typo3BetterApi\Event\Events\ExtConfigClassListFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\ExtConfigInitEvent;
use LaborDigital\Typo3BetterApi\Event\Events\ExtConfigLoadedEvent;
use LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionInterface;
use LaborDigital\Typo3BetterApi\ExtConfig\OptionList\ExtConfigOptionList;
use LaborDigital\Typo3BetterApi\NamingConvention\Naming;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\EventBus\EventBusInterface;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class ExtConfigService implements LazyEventSubscriberInterface
{
    
    /**
     * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
     */
    protected $container;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
     */
    protected $typoContext;
    
    /**
     * @var \Neunerlei\EventBus\EventBusInterface
     */
    protected $eventBus;
    
    /**
     * A list of all registered extensions
     * @var array
     */
    protected static $registeredExtensions = [];
    
    /**
     * ExtConfigService constructor.
     *
     * @param \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface $container
     * @param \LaborDigital\Typo3BetterApi\TypoContext\TypoContext          $typoContext
     * @param \Neunerlei\EventBus\EventBusInterface                         $eventBus
     */
    public function __construct(TypoContainerInterface $container, TypoContext $typoContext, EventBusInterface $eventBus)
    {
        $this->container = $container;
        $this->typoContext = $typoContext;
        $this->eventBus = $eventBus;
    }
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(ExtConfigInitEvent::class, '__init');
    }
    
    /**
     * Internal helper to register an extension using the betterExtConfig() function
     *
     * @param string $extKeyWithVendor
     * @param string $configurationClass
     * @param array  $options
     *
     * @see betterExtConfig()
     * @internal
     */
    public static function __registerExtension(string $extKeyWithVendor, string $configurationClass, array $options = []): void
    {
        static::$registeredExtensions[] = [$extKeyWithVendor, $configurationClass, $options];
    }
    
    /**
     * EventHandler which collect's the registered ext config objects and initializes the option configuration.
     *
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     */
    public function __init()
    {
        
        // Create the list of available configuration objects
        $e = new ExtConfigBeforeLoadEvent(static::$registeredExtensions);
        $this->eventBus->dispatch($e);
        $rawConfigList = $e->getRawConfigList();
        
        // Pass 1: Validate configuration
        $configList = [];
        foreach ($rawConfigList as $rawConfig) {
            [$extKeyWithVendor, $configurationClass, $options] = $rawConfig;
            // Parse extKey and vendor
            $extKey = Naming::extkeyWithoutVendor($extKeyWithVendor);
            $vendor = ucfirst(Naming::vendorFromExtkey($extKeyWithVendor));
            $isExtension = in_array(ExtConfigExtensionInterface::class, class_implements($configurationClass));
            $isConfiguration = in_array(ExtConfigInterface::class, class_implements($configurationClass));
            
            // Validate the class
            if (isset($configList[$configurationClass])) {
                // Ignore double registration
                if ($configList[$configurationClass]['extKey'] === $extKey &&
                    $configList[$configurationClass]['vendor'] === $vendor) {
                    continue;
                }
                throw new ExtConfigException('The given class: ' . $configurationClass . ' for: ' . $extKeyWithVendor . ' was already registered!');
            }
            if (!class_exists($configurationClass)) {
                throw new ExtConfigException('The given class: ' . $configurationClass . ' for: ' . $extKeyWithVendor . ' was not found!');
            }
            if (!$isExtension && !$isConfiguration) {
                throw new ExtConfigException('The given class: ' . $configurationClass . ' for: ' . $extKeyWithVendor . ' has to implement the ' . ExtConfigInterface::class . ' interface!');
            }
            
            // Store configuration
            $options['class'] = $configurationClass;
            $options['extKey'] = $extKey;
            $options['vendor'] = $vendor;
            $options['isExtension'] = $isExtension;
            $options['isConfiguration'] = $isConfiguration;
            $configList[$configurationClass] = $options;
        }
        
        // Sort the configuration list
        $configList = ConfigSorter::sortByDependencies($configList);
        
        // Create the ext config context
        $context = $this->container->get(ExtConfigContext::class, ['args' => [$this->typoContext]]);
        
        // Create extension registry
        $extensionRegistry = $context->ExtensionRegistry;
        
        // Allow filtering
        $e = new ExtConfigClassListFilterEvent($configList, $context, $extensionRegistry);
        $this->eventBus->dispatch($e);
        $extensionRegistry = $e->getExtensionRegistry();
        $configList = $e->getConfigList();
        $context = $e->getContext();
        
        // Pass 2: Collect extensions
        foreach ($configList as $config) {
            if (!$config['isExtension']) {
                continue;
            }
            
            // Prepare the context
            $context->setExtKey($config['extKey']);
            $context->setVendor($config['vendor']);
            
            // Call the extension handler
            call_user_func([$config['class'], 'extendExtConfig'], $extensionRegistry, $context);
        }
        
        // Clear the context
        $context->setExtKey('LIMBO');
        $context->setVendor('LIMBO');
        
        // Call the extension handlers
        $extensionRegistry->notifyExtensionHandlers();
        
        // Create the option list
        $optionList = $this->container->get(ExtConfigOptionList::class, ['args' => [$context]]);
        $context->__injectOptionList($optionList);
        
        // Pass 3: Apply the configuration passes
        foreach ($configList as $config) {
            if (!$config['isConfiguration']) {
                continue;
            }
            
            /** @var \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigInterface $i */
            $i = $this->container->get($config['class']);
            
            // Prepare the context
            $context->setExtKey($config['extKey']);
            $context->setVendor($config['vendor']);
            
            // Apply the configuration
            $i->configure($optionList, $context);
        }
        
        // Clear the context
        $context->setExtKey('LIMBO');
        $context->setVendor('LIMBO');
        
        // Allow filtering
        $this->eventBus->dispatch(new ExtConfigLoadedEvent($context));
    }
}
