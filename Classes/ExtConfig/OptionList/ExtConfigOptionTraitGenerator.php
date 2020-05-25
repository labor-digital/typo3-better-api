<?php
/**
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
 * Last modified: 2020.03.21 at 17:02
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\OptionList;

use LaborDigital\Typo3BetterApi\CoreModding\CodeGeneration\CodeGenerationHelperTrait;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionHandlerInterface;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtConfigOptionInterface;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;
use ReflectionClass;

class ExtConfigOptionTraitGenerator implements ExtConfigExtensionHandlerInterface
{
    use CodeGenerationHelperTrait;
    
    public const TRAIT_NAME = ExtConfigOptionListTrait::class;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
     */
    protected $context;
    
    /**
     * The list of all option classes by their option name
     * The value is populated by the generate() method
     * @var array
     */
    protected $options;
    
    /**
     * @inheritDoc
     */
    public function __construct(ExtConfigContext $context)
    {
        $this->context = $context;
    }
    
    /**
     * Returns either the class name for an option name or null
     * if none was registered
     *
     * @param string $optionName The name of the option to check for
     *
     * @return string|null
     */
    public function getClassNameForOption(string $optionName): ?string
    {
        return $this->options[$optionName];
    }
    
    /**
     * @inheritDoc
     */
    public function generate(array $extensions): void
    {
        // Generate a hash for the loaded extensions
        $hash = md5(\GuzzleHttp\json_encode($extensions));
        
        // Get or generate the options
        $fileName = "OptionListOptions-$hash.php";
        if (!$this->context->Fs->hasFile($fileName)) {
            $this->options = $this->generateOptionClassList($extensions);
            $this->context->Fs->setFileContent($fileName, $this->options);
        } else {
            $this->options = $this->context->Fs->getFileContent($fileName);
        }
        
        // Compile if the trait does not exist
        $fileName = "OptionListTrait-$hash.php";
        if (!$this->context->Fs->hasFile($fileName)) {
            $methods = [];
            foreach ($this->options as $name => $class) {
                $methods[] = $this->makeOptionSrc($name, $class);
            }
            $this->context->Fs->setFileContent($fileName, $this->makeTraitSrc($methods));
        }
        
        // Include the trait if it does not exist yet
        if (!trait_exists(static::TRAIT_NAME)) {
            $this->context->Fs->includeFile($fileName);
        }
    }
    
    /**
     * Returns the list of the generated options based on the given extensions
     *
     * @param array $extensions
     *
     * @return array
     */
    protected function generateOptionClassList(array $extensions): array
    {
        // Generate the list of options
        $options = [];
        foreach ($extensions as $extension) {
            if (is_string($extension['options']['optionName'])) {
                $optionName = $extension['options']['optionName'];
            } else {
                $optionName = Path::classBasename($extension['class']);
                $optionName = preg_replace('~((ext)?config)?(options?)?$~si', '', $optionName);
                $optionName = Inflector::toCamelBack($optionName);
            }
            $options[$optionName] = $extension['class'];
        }
        return $options;
    }
    
    /**
     * Builds the method source code for a single option
     *
     * @param string $optionName
     * @param string $className
     *
     * @return string
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     */
    protected function makeOptionSrc(string $optionName, string $className): string
    {
        // Validate class
        if (!class_exists($className)) {
            throw new ExtConfigException('Failed to build body for option: ' . $optionName . ' because the registered class: ' . $className . ' does not exist!');
        }
        if (!in_array(ExtConfigOptionInterface::class, class_implements($className))) {
            throw new ExtConfigException('Failed to build body for option: ' . $optionName . ' because the registered class: ' . $className . ' does not implement the ' . ExtConfigOptionInterface::class . ' interface!');
        }
        
        // Get description from the class
        $ref = new ReflectionClass($className);
        $desc = $this->sanitizeDesc($ref->getDocComment());
        return "
	/**
	 * $desc
	 * @return \\$className
	 */
	public function $optionName(): \\$className {
		return \$this->getOrCreateOptionInstance(\\$className::class);
	}
";
    }
    
    /**
     * Creates the outer body of the generated trait
     *
     * @param array $methods
     *
     * @return string
     */
    protected function makeTraitSrc(array $methods): string
    {
        $body = implode(PHP_EOL . PHP_EOL, $methods);
        $namespace = Path::classNamespace(static::TRAIT_NAME);
        $className = Path::classBasename(static::TRAIT_NAME);
        return "<?php
namespace $namespace;

trait $className {
$body
}
";
    }
}
