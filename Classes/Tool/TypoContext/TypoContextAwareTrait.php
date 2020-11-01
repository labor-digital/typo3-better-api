<?php
/*
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
 * Last modified: 2020.08.28 at 10:31
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\TypoContext;


use Psr\Container\ContainerInterface;

trait TypoContextAwareTrait
{
    /**
     * @var \LaborDigital\T3BA\Tool\TypoContext\TypoContext
     */
    protected $__typoContext;

    /**
     * Injects the typo context instance
     *
     * @param   \LaborDigital\T3BA\Tool\TypoContext\TypoContext  $typoContext
     */
    public function injectTypoContext(TypoContext $typoContext): void
    {
        $this->__typoContext = $typoContext;
    }

    /**
     * Returns the typo context instance
     *
     * @return TypoContext
     */
    protected function TypoContext(): TypoContext
    {
        if (isset($this->__typoContext)) {
            return $this->__typoContext;
        }
        if (method_exists($this, 'Container')) {
            $container = $this->Container();
            if ($container instanceof ContainerInterface) {
                return $this->__typoContext = $container->get(TypoContext::class);
            }
        }

        return $this->__typoContext = TypoContext::getInstance();
    }
}
