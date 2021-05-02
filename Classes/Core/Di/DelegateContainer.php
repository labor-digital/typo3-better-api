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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Core\Di;


use InvalidArgumentException;
use LaborDigital\T3ba\Core\Util\SingletonInstanceTrait;
use LogicException;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DelegateContainer implements ContainerInterface
{
    use SingletonInstanceTrait;
    
    /**
     * @var \LaborDigital\T3ba\Core\Di\MiniContainer
     */
    protected $internal;
    
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $symfony;
    
    /**
     * @var \TYPO3\CMS\Core\DependencyInjection\FailsafeContainer
     */
    protected $failsafe;
    
    /**
     * Allows the outside world to provide a container instance to this delegate
     *
     * @param   string                                  $type
     * @param   \Psr\Container\ContainerInterface|null  $container
     */
    public function setContainer(string $type, ?ContainerInterface $container): void
    {
        if (! in_array($type, ['internal', 'symfony', 'failsafe'], true)) {
            throw new InvalidArgumentException('Invalid container type given!');
        }
        $this->$type = $container;
    }
    
    /**
     * Allows the outside world to register a concrete service on the container
     *
     * @param $id
     * @param $concrete
     */
    public function set($id, $concrete): void
    {
        if (isset($this->symfony)) {
            $this->symfony->set($id, $concrete);
        } else {
            $this->internal->set($id, $concrete);
        }
    }
    
    /**
     * Returns the symfony container instance or null if there is none.
     *
     * The script will always prefer the container implementation on GeneralUtility::getContainer()
     * before using our internal reference, so we can handle the late boot makeCurrent stuff correctly.
     *
     * @return \Symfony\Component\DependencyInjection\Container|null
     */
    public function getSymfony(): ?Container
    {
        try {
            $c = GeneralUtility::getContainer();
            
            if ($c instanceof Container) {
                return $c;
            }
            
        } catch (LogicException $e) {
        }
        
        return $this->symfony ?? null;
    }
    
    /**
     * Returns the internal mini container for our root services
     *
     * @return \LaborDigital\T3ba\Core\Di\MiniContainer
     */
    public function getInternal(): MiniContainer
    {
        return $this->internal;
    }
    
    /**
     * @inheritDoc
     * @return mixed
     */
    public function get($id)
    {
        if (isset($this->failsafe) && $this->failsafe->has($id)) {
            return $this->failsafe->get($id);
        }
        
        $symfony = $this->getSymfony();
        if ($symfony && $symfony->has($id)) {
            return $symfony->get($id);
        }
        
        if (isset($this->internal) && $this->internal->has($id)) {
            return $this->internal->get($id);
        }
        
        if ($symfony) {
            return $this->symfony->get($id);
        }
        
        throw new ServiceNotFoundException($id);
    }
    
    /**
     * @inheritDoc
     */
    public function has($id): bool
    {
        if (isset($this->failsafe) && $this->failsafe->has($id)) {
            return true;
        }
        
        $symfony = $this->getSymfony();
        if ($symfony && $symfony->has($id)) {
            return true;
        }
        
        if (isset($this->internal) && $this->internal->has($id)) {
            return true;
        }
        
        return false;
    }
}
