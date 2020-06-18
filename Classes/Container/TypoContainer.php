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
 * Last modified: 2020.03.19 at 13:08
 */

namespace LaborDigital\Typo3BetterApi\Container;

use Error;
use Exception;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Container\Container;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\Exception\UnknownClassException;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;

class TypoContainer implements TypoContainerInterface, SingletonInterface
{
    
    /**
     * The instance of this container as a singleton
     *
     * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
     */
    protected static $instance;
    
    /**
     * Holds the instance of the typo3 extbase object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;
    
    /**
     * A list of id's and their states to prevent multiple requests
     *
     * @var array
     */
    protected $hasStates = [];
    
    /**
     * @inheritDoc
     */
    public function has($id)
    {
        if (isset($this->hasStates[$id])) {
            return $this->hasStates[$id];
        }
        
        // Check if the container can create the object
        try {
            $this->getObjectManager()->getEmptyObject($id);
            $state = true;
        } catch (UnknownClassException $e) {
            $state = false;
        }
        
        // Return the state
        return $this->hasStates[$id] = $state;
    }
    
    /**
     * @inheritDoc
     */
    public function get($id, array $options = [])
    {
        try {
            $options = Options::make($options, [
                'args' => [
                    'type'    => 'array',
                    'default' => [],
                ],
                'gu'   => [
                    'type'    => 'bool',
                    'default' => false,
                ],
            ]);
            array_unshift($options['args'], $id);
            
            return $options['gu'] === true
                ? GeneralUtility::makeInstance(...$options['args'])
                :
                $this->getObjectManager()->get(...$options['args']);
        } catch (Exception | Error $e) {
            // This is probably something that broke in the cached class schema -> Flush the cache
            $this->getObjectManager()
                 ->get(CacheManager::class)
                 ->getCache(ReflectionService::CACHE_IDENTIFIER)
                 ->flush();
            
            // Throw an exception
            throw new TypoContainerException(
                'Exception while resolving object: ' . $id . ': ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
    
    /**
     * @inheritDoc
     */
    public function set(string $class, SingletonInterface $instance): TypoContainerInterface
    {
        GeneralUtility::setSingletonInstance($class, $instance);
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function setClassFor(string $interface, string $class): TypoContainerInterface
    {
        $this->get(Container::class, ['gu'])->registerImplementation($interface, $class);
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function setXClassFor(string $classToOverride, string $classToOverrideWith): TypoContainerInterface
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][$classToOverride] = [
            'className' => $classToOverrideWith,
        ];
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function getObjectManager(): ObjectManagerInterface
    {
        if (isset($this->objectManager)) {
            return $this->objectManager;
        }
        
        return $this->objectManager = $this->get(ObjectManager::class, ['gu' => true]);
    }
    
    /**
     * Returns the singleton instance of this class
     *
     * @return \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
     */
    public static function getInstance(): TypoContainerInterface
    {
        // Check if we already have a instance of ourselves
        if (isset(static::$instance)) {
            return static::$instance;
        }
        
        // Create a new instance
        return static::$instance = GeneralUtility::makeInstance(static::class);
    }
}
