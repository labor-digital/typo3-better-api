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
 * Last modified: 2021.05.20 at 13:28
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\TypoContext;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Core\VarFs\Mount;
use LaborDigital\T3ba\Tool\OddsAndEnds\ReflectionUtil;
use Neunerlei\PathUtil\Path;
use ReflectionClass;

class AutocompleteGenerator implements NoDiInterface
{
    
    protected const CONTEXT_CLASS = TypoContext::class;
    
    /**
     * @var \LaborDigital\T3ba\Core\VarFs\Mount
     */
    protected $fs;
    
    /**
     * FieldPresetAutocompleteGenerator constructor.
     *
     * @param   \LaborDigital\T3ba\Core\VarFs\Mount  $fsMount
     */
    public function __construct(Mount $fsMount)
    {
        $this->fs = $fsMount;
    }
    
    /**
     * Receives the list of registered facet classes by their short name and generates a
     * autocomplete helper dummy TypoContext class into the var fs directory
     *
     * This is a convenience feature for dev purposes only!
     *
     * @param   array  $facetClasses
     */
    public function generate(array $facetClasses): void
    {
        $filename = '/AutocompleteHelper/TypoContextWithFacets.php';
        
        if ($this->fs->hasFile($filename)) {
            return;
        }
        
        $methods = [];
        foreach ($facetClasses as $shortName => $facetClass) {
            $methods[] = $this->makeMethodSrc($facetClass, $shortName);
        }
        
        $source = $this->makeClassSrc($methods);
        
        $this->fs->setFileContent($filename, $source);
    }
    
    /**
     * Builds the source code for a single facet method
     *
     * @param   string  $facetClass
     * @param   string  $shortName
     *
     * @return string
     */
    protected function makeMethodSrc(string $facetClass, string $shortName): string
    {
        $comment = ReflectionUtil::sanitizeDesc((new ReflectionClass($facetClass))->getDocComment());
        $comment = array_map(static function ($line) { return '     * ' . $line; }, $comment);
        
        return '
    /**
' . implode(PHP_EOL, $comment) . '
     * @return \\' . $facetClass . '
     */
     public function ' . $shortName . '(): \\' . $facetClass . '
     {
        /** Autocomplete Helper - Handled by TypoContext internally */
     }';
    }
    
    /**
     * Generates the source code for the dummy clone to extend the typoContext class
     *
     * @param   array  $methods
     *
     * @return string
     */
    protected function makeClassSrc(array $methods): string
    {
        $namespace = Path::classNamespace(static::CONTEXT_CLASS);
        $className = Path::classBasename(static::CONTEXT_CLASS);
        
        return '<?php
declare(strict_types=1);

namespace ' . $namespace . ';

/**
 * ATTENTION: This is a autocomplete helper! It will only be generated in a development environment!
 * TypoContext will always resolve the facets using a magic __call() method, this exists only to show your IDE which facets there are.
 * @see \\' . static::CONTEXT_CLASS . '
 */
class ' . $className . ' {

' . implode(PHP_EOL . PHP_EOL, $methods) . '

}
';
    }
}