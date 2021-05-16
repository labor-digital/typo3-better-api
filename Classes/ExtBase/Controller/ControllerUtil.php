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
 * Last modified: 2021.05.10 at 17:46
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtBase\Controller;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ControllerUtil implements NoDiInterface
{
    /**
     * Checks if the given $controller is of type ActionController or throws an exception
     *
     * @param $controller
     *
     * @throws \LaborDigital\T3ba\ExtBase\Controller\NotAControllerException
     */
    public static function requireActionController($controller): void
    {
        if (! $controller instanceof ActionController) {
            throw new NotAControllerException('To use this trait you have to call it in an ActionController action!');
        }
    }
}
