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
 * Last modified: 2021.02.10 at 21:27
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig\Abstracts;


use LaborDigital\T3BA\Core\Di\PublicServiceInterface;
use LaborDigital\T3BA\ExtConfig\Interfaces\ElementKeyProviderInterface;
use LaborDigital\T3BA\ExtConfig\Traits\ExtConfigContextTrait;
use Neunerlei\Configuration\Handler\AbstractGroupConfigHandler;
use Neunerlei\PathUtil\Path;

abstract class AbstractGroupExtConfigHandler extends AbstractGroupConfigHandler implements PublicServiceInterface
{
    use ExtConfigContextTrait;

    /**
     * Helper to generate the element key for a given class, that automatically takes care
     * for classes implementing the ElementKeyProviderInterface
     *
     * @param   string         $class            The class to generate the element key for
     * @param   callable|null  $postProcessor    An optional post processor which will be executed if the class
     *                                           does not implement the ElementKeyProviderInterface
     * @param   bool           $useOnlyBaseName  By default only the class basename is used for key generation.
     *                                           If you set this to false, the whole class including the namespace will
     *                                           be used
     *
     * @return string
     * @see \LaborDigital\T3BA\ExtConfig\Interfaces\ElementKeyProviderInterface
     */
    protected function getElementKeyForClass(
        string $class,
        ?callable $postProcessor = null,
        bool $useOnlyBaseName = true
    ): string {
        if (in_array(ElementKeyProviderInterface::class, class_implements($class), true)) {
            return $class('getElementKey');
        }

        if ($useOnlyBaseName) {
            $class = Path::classBasename($class);
        }

        if ($postProcessor === null) {
            return $class;
        }

        return $postProcessor($class);
    }
}
