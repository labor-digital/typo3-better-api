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
 * Last modified: 2021.06.11 at 17:18
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfig\Abstracts;


use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\ExtConfig\Interfaces\ElementKeyProviderInterface;
use LaborDigital\T3ba\ExtConfig\Traits\ExtConfigContextTrait;
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
     * @param   callable|null  $postProcessor    An optional post processor which can alter the processing.
     *                                           It will receive the following arguments:
     *                                           string $elementClass - The original class name
     *                                           string $elementKey - The generated element key
     *                                           bool $hasElementKeyProvider - True if the class provided the element key itself.
     *                                           This means you should be able to safely ignore the key.
     *                                           The callback MUST return the $elementKey, even if it did not modify it.
     * @param   bool           $useOnlyBaseName  By default only the class basename is used for key generation.
     *                                           If you set this to false, the whole class including the namespace will
     *                                           be used
     *
     * @return string
     * @see \LaborDigital\T3ba\ExtConfig\Interfaces\ElementKeyProviderInterface
     */
    protected function getElementKeyForClass(
        string $class,
        ?callable $postProcessor = null,
        bool $useOnlyBaseName = true
    ): string
    {
        $elementKey = $class;
        if (in_array(ElementKeyProviderInterface::class, class_implements($class), true)) {
            $elementKey = call_user_func([$class, 'getElementKey']);
            $hasElementKeyProvider = true;
        } elseif ($useOnlyBaseName) {
            $elementKey = Path::classBasename($class);
        }
        
        if ($postProcessor === null) {
            return $elementKey;
        }
        
        return $postProcessor($class, $elementKey, isset($hasElementKeyProvider));
    }
}
