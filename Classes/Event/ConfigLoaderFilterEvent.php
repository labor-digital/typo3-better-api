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


namespace LaborDigital\T3BA\Event;


use Neunerlei\Configuration\Loader\Loader;

class ConfigLoaderFilterEvent
{
    
    /**
     * The loader instance being created
     *
     * @var \Neunerlei\Configuration\Loader\Loader
     */
    protected $loader;
    
    /**
     * ConfigLoaderFilterEvent constructor.
     *
     * @param   \Neunerlei\Configuration\Loader\Loader  $loader
     */
    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }
    
    /**
     * Returns the loader instance being created
     *
     * @return \Neunerlei\Configuration\Loader\Loader
     */
    public function getLoader(): Loader
    {
        return $this->loader;
    }
    
    /**
     * Allows you to replace the loader instance being created
     *
     * @param   \Neunerlei\Configuration\Loader\Loader  $loader
     *
     * @return ConfigLoaderFilterEvent
     */
    public function setLoader(Loader $loader): ConfigLoaderFilterEvent
    {
        $this->loader = $loader;
        
        return $this;
    }
    
}
