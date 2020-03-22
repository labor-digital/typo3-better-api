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

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\Table\Preset;


use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormField;
use LaborDigital\Typo3BetterApi\BackendForms\FormPresets\AbstractFormPreset;
use LaborDigital\Typo3BetterApi\BackendForms\FormPresets\FormPresetInterface;
use LaborDigital\Typo3BetterApi\CoreModding\CodeGeneration\CodeGenerationHelperTrait;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionHandlerInterface;
use Neunerlei\PathUtil\Path;
use ReflectionClass;
use ReflectionMethod;

class FieldPresetApplierTraitGenerator implements ExtConfigExtensionHandlerInterface {
	use CodeGenerationHelperTrait;
	
	public const TRAIT_NAME = FieldPresetApplierTrait::class;
	
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
	 * Returns the list of all generated presets based on the given extensions
	 *
	 * @param array $extensions
	 *
	 * @return array
	 * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
	 */
	public function getAllPresets(array $extensions): array {
		// Check if we have the entry already cached
		$cacheKey = "ext-config-preset-list-entries";
		if ($this->context->GeneralCache->has($cacheKey))
			return $this->context->GeneralCache->get($cacheKey);
		
		// Collect the presets via reflection
		$presets = [];
		foreach ($extensions as $class => $args) {
			// Validate class
			if (!class_exists($class))
				throw new ExtConfigException("Failed to register a form field preset, because the given class: " . $class . " does not exist!");
			if (!in_array(FormPresetInterface::class, class_implements($class)))
				throw new ExtConfigException("Failed to register a form field preset, because the given class: " . $class . " does not implement the required interface: " . FormPresetInterface::class . "!");
			
			// Loop through methods
			$ref = new ReflectionClass($class);
			foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
				
				// Ignore inherited classes
				if (in_array($method->getDeclaringClass()->getName(), [AbstractFormPreset::class])) continue;
				
				// Avoid overlap
				if (isset($presets[$method->getName()]))
					throw new ExtConfigException("Can't redefine a preset with name " . $method->getName() . ", because it was already defined by: " . $presets[$method->getName()]["class"]);
				
				// Register method as handler
				$presets[$method->getName()] = $class;
			}
		}
		
		// Store and return the presets
		$this->context->GeneralCache->set($cacheKey, $presets);
		return $presets;
	}
	
	/**
	 * @inheritDoc
	 */
	public function generate(array $extensions): void {
		// Compile if the trait does not exist
		$fileName = "FieldPresetApplierTrait-" . md5(\GuzzleHttp\json_encode($extensions)) . ".php";
		if (!$this->context->Fs->hasFile($fileName)) {
			$methods = [];
			$presets = $this->getAllPresets($extensions);
			foreach ($presets as $name => $class) $methods[] = $this->makeOptionSrc(new ReflectionMethod($class, $name));
			$this->context->Fs->setFileContent($fileName, $this->makeTraitSrc($methods));
		}
		
		// Include the trait if it does not exist yet
		if (!trait_exists(static::TRAIT_NAME))
			$this->context->Fs->includeFile($fileName);
		
	}
	
	/**
	 * Builds the source code for a single preset method
	 *
	 * @param \ReflectionMethod $method
	 *
	 * @return string
	 */
	protected function makeOptionSrc(ReflectionMethod $method): string {
		$args = $this->generateMethodArgs($method);
		$desc = $method->getDocComment();
		$desc = $this->sanitizeDesc($desc);
		$class = $method->getDeclaringClass()->getName();
		$fieldClass = AbstractFormField::class;
		$key = $method->getName();
		return "
	/**
	 * $desc
	 * @return \\$fieldClass
	 *
	 * @see \\$class::$key();
	 */
	public function $key($args): \\$fieldClass {
		return \$this->callHandlerInstance(\\$class::class, \"$key\", func_get_args());
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