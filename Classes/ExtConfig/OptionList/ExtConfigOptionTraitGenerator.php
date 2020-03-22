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

class ExtConfigOptionTraitGenerator implements ExtConfigExtensionHandlerInterface {
	use CodeGenerationHelperTrait;
	
	public const TRAIT_NAME = ExtConfigOptionListTrait::class;
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
	 */
	protected $context;
	
	/**
	 * @inheritDoc
	 */
	public function __construct(ExtConfigContext $context) {
		$this->context = $context;
	}
	
	/**
	 * Returns the list of the generated options based on the given extensions
	 *
	 * @param array $extensions
	 *
	 * @return array
	 */
	public function getAllOptions(array $extensions): array {
		// Check if we have the entries already cached
		$cacheKey = "ext-config-option-list-options";
		if ($this->context->GeneralCache->has($cacheKey))
			return $this->context->GeneralCache->get($cacheKey);
		
		// Generate the list of options
		$options = [];
		foreach ($extensions as $extension) {
			if (is_string($extension["options"]["optionName"]))
				$optionName = $extension["options"]["optionName"];
			else {
				$optionName = Path::classBasename($extension["class"]);
				$optionName = preg_replace("~((ext)?config)?(options?)?$~si", "", $optionName);
				$optionName = Inflector::toCamelBack($optionName);
			}
			$options[$optionName] = $extension["class"];
		}
		
		// Store and return the options
		$this->context->GeneralCache->set($cacheKey, $options);
		return $options;
	}
	
	/**
	 * @inheritDoc
	 */
	public function generate(array $extensions): void {
		$options = $this->getAllOptions($extensions);
		
		// Compile if the trait does not exist
		$fileName = "OptionListTrait-" . md5(\GuzzleHttp\json_encode($options)) . ".php";
		if (!$this->context->Fs->hasFile($fileName)) {
			$methods = [];
			foreach ($options as $name => $class) $methods[] = $this->makeOptionSrc($name, $class);
			$this->context->Fs->setFileContent($fileName, $this->makeTraitSrc($methods));
		}
		
		// Include the trait if it does not exist yet
		if (!trait_exists(static::TRAIT_NAME))
			$this->context->Fs->includeFile($fileName);
		
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
	protected function makeOptionSrc(string $optionName, string $className): string {
		// Validate class
		if (!class_exists($className))
			throw new ExtConfigException("Failed to build body for option: " . $optionName . " because the registered class: " . $className . " does not exist!");
		if (!in_array(ExtConfigOptionInterface::class, class_implements($className)))
			throw new ExtConfigException("Failed to build body for option: " . $optionName . " because the registered class: " . $className . " does not implement the " . ExtConfigOptionInterface::class . " interface!");
		
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
	protected function makeTraitSrc(array $methods): string {
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