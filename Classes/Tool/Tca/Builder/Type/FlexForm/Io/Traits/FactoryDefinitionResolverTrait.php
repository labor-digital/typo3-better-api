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


namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Io\Traits;


use LaborDigital\T3ba\Core\Di\CommonServices;
use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Io\GeneralUtilityAdapter;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Io\InvalidFlexFormException;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Io\MissingFlexFormFileException;
use Neunerlei\FileSystem\Fs;
use Neunerlei\PathUtil\Path;

trait FactoryDefinitionResolverTrait
{
    abstract protected function cs(): CommonServices;
    
    /**
     * Helper to resolve the string into a flex form definition array.
     * The given $definition can either be a full flex form definition (you would normally write in an .xml file),
     * a file:EXT:YourExt... path to your flex form definition or just file:myFlexForm.xml if you want to load
     * the file from your current extensions Configuration/FlexForms/ directory.
     *
     * The result is the already parsed array representation.
     *
     * @param   string                                         $definition
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext  $context
     *
     * @return array
     * @throws \LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Io\InvalidFlexFormException
     */
    protected function resolveDefinitionToArray(string $definition, ExtConfigContext $context): array
    {
        $definition = trim($definition);
        $definition = $this->resolveFlexFormFileContent($definition, $context);
        $def = GeneralUtilityAdapter::xml2arrayWithoutCache($definition);
        
        if (! is_array($def)) {
            throw new InvalidFlexFormException('Error while parsing flex form definition: "' .
                                               substr($definition, 0, 50) . '...". Error: ' . $def);
        }
        
        return $this->resolveSheetsArray($def);
    }
    
    /**
     * Internal helper to resolve a file definition into an actual flex form definition string.
     *
     * @param   string                                         $definition
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext  $context
     *
     * @return string
     * @throws \LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Io\MissingFlexFormFileException
     */
    protected function resolveFlexFormFileContent(string $definition, ExtConfigContext $context): string
    {
        // Ignore if this is no file definition
        if (stripos($definition, 'file:') !== 0) {
            return $definition;
        }
        
        $path = $this->cs()->typoContext->path();
        $filePath = substr($definition, 5);
        $extKey = $context->getExtKey();
        
        // We try multiple locations where the file could be located
        $pathsToTry = [
            $filePath,
            'FILE:EXT:' . Path::join($extKey, '/Configuration/FlexForms/', $filePath),
            'FILE:EXT:' . Path::join($extKey, '/Configuration/', $filePath),
            'FILE:EXT:' . Path::join($extKey, $filePath),
            'FILE:EXT:' . Path::join($extKey, '/Configuration/FlexForms/', basename($filePath)),
            'FILE:EXT:' . Path::join($extKey, '/Configuration/FlexForm/', $filePath),
            'FILE:EXT:' . Path::join($extKey, '/Configuration/FlexForm/', basename($filePath)),
        ];
        unset($filePath);
        
        foreach ($pathsToTry as $filePath) {
            $filePath = $path->typoPathToRealPath($filePath);
            if (Fs::exists($filePath)) {
                return Fs::readFile($filePath);
            }
        }
        
        throw new MissingFlexFormFileException(
            'Could not load the flex form file at: ' . $definition . ', because the file does not exist. I tried: '
            . implode(', ', $pathsToTry));
    }
    
    /**
     * Makes sure the "sheets" master array exists and tries to handle malformed flex-form definitions
     * on the fly.
     *
     * @param   array  $def
     *
     * @return array
     * @throws \LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Io\InvalidFlexFormException
     */
    protected function resolveSheetsArray(array $def): array
    {
        if (isset($def['sheets']) && is_array($def['sheets'])) {
            return $def;
        }
        
        if (isset($def['sDEF']) && is_array($def['sDEF'])) {
            return array_merge($def, ['sheets' => [$def], 'sDEF' => null]);
        }
        
        if (isset($def['ROOT']) && is_array($def['ROOT'])) {
            return array_merge($def, ['sheets' => ['sDEF' => $def], 'ROOT' => null]);
        }
        
        throw new InvalidFlexFormException('Could not load flex form, as it does not define any "sheets".');
        
    }
}
