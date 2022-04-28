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


namespace LaborDigital\T3ba\Tool\Tca\ContentType\Domain;


use InvalidArgumentException;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class AbstractDataModel extends AbstractEntity implements \ArrayAccess
{
    /**
     * The raw database array that was used to create this model
     *
     * @var array
     */
    protected $__raw;
    
    /**
     * Contains The unpacked flex form data by the name of the flex form field name
     *
     * @var array
     */
    protected $__flex = [];
    
    /**
     * This property helps to avoid that __isset is called recursively inside __get
     *
     * @var bool
     */
    protected $__recursiveLookup = false;
    
    /**
     * Returns the raw database array that was used to create this model
     *
     * @return array
     */
    public function getRaw(): array
    {
        return $this->__raw;
    }
    
    /**
     * Can be used to access the values of any flex form field in your configuration.
     * By default this method returns the full flex form array (if no path is given).
     * The method will combine all available flex form fields into a single array, that contains the matching field
     * names. You can use the $path attribute to select a specific path in your configuration
     *
     * @param   array|string|null  $path     If empty the whole flex form array is returned. Can be used to find a
     *                                       sub-section of the configuration.
     * @param   null|mixed         $default  Can be used to define a default value that is returned inf the given path
     *                                       was not found.
     *
     * @return array|mixed|null
     */
    public function getFlex($path = null, $default = null)
    {
        if (empty($path)) {
            return $this->__flex;
        }
        
        return Arrays::getPath($this->__flex, $path, $default);
    }
    
    /**
     * Block all writing on magic properties
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        throw new InvalidArgumentException('This model has only readable magic properties!');
    }
    
    /**
     * Allow magic access to all the raw properties of this model
     *
     * @param $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        $this->__recursiveLookup = true;
        try {
            if (isset($this->$name)) {
                return $this->$name;
            }
        } finally {
            $this->__recursiveLookup = false;
        }
        
        $raw = $this->getRaw();
        
        if (isset($raw[$name])) {
            return $raw[$name];
        }
        
        $name = Inflector::toDatabase($name);
        
        return $raw[$name] ?? null;
    }
    
    /**
     * Allow magic state lookup for raw properties
     *
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        if ($this->__recursiveLookup) {
            return false;
        }
        
        return $this->__get($name) !== null;
    }
    
    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }
    
    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }
    
    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        throw new InvalidArgumentException('This model has only readable magic properties!');
    }
    
    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        throw new InvalidArgumentException('This model has only readable magic properties!');
    }
    
    
}
