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
 * Last modified: 2021.01.30 at 12:47
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\FieldPreset;


use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use ReflectionClass;
use ReflectionMethod;

class ListGenerator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The list of presets and their
     *
     * @var array
     */
    protected $presets = [];

    /**
     * Generates the list of preset methods/names and adds them to the list of all registered presets
     *
     * @param   string  $class          The name of the class defining the presets.
     *                                  The class MUST implement the FieldPresetInterface
     * @param   bool    $allowOverride  If set to true presets can be overwritten
     *
     * @see \LaborDigital\T3BA\Tool\Tca\Builder\FieldPreset\FieldPresetInterface
     */
    public function registerClass(string $class, bool $allowOverride = false): void
    {
        $ref = new ReflectionClass($class);
        foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // Ignore inherited classes
            if ($method->getDeclaringClass()->getName() !== $class) {
                continue;
            }

            // Ignore all methods that don't start with "apply"
            $name = $method->getName();
            if (strpos($name, 'apply') !== 0) {
                continue;
            }
            $name = lcfirst(substr($name, 5));

            // Avoid overlap
            if (! $allowOverride && isset($this->presets[$name])) {
                $this->logger->error(
                    'Skipped a preset with name ' . $name . ', defined as: '
                    . $ref->getName() . '::' . $method->getName()
                    . ', because it was already defined as: '
                    . $this->presets[$name][0] . '::' . $this->presets[$name][1]
                );
                continue;
            }

            $this->presets[$name] = [$ref->getName(), $method->getName()];
        }
    }

    /**
     * Returns the list of all registered presets with their defining class names
     *
     * @return array
     */
    public function getPresets(): array
    {
        return $this->presets;
    }

}
