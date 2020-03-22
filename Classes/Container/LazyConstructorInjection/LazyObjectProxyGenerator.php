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
 * Last modified: 2020.03.19 at 13:08
 */

namespace LaborDigital\Typo3BetterApi\Container\LazyConstructorInjection;


use LaborDigital\Typo3BetterApi\CoreModding\CodeGeneration\CodeGenerationHelperTrait;
use LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;
use ReflectionClass;
use ReflectionMethod;

class LazyObjectProxyGenerator {
	use CodeGenerationHelperTrait;
	
	/**
	 * The static instance of myself
	 * @var \LaborDigital\Typo3BetterApi\Container\LazyConstructorInjection\LazyObjectProxyGenerator
	 */
	protected static $self;
	
	/**
	 * The instance of the file system we use
	 * @var \LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs
	 */
	protected $fs;
	
	/**
	 * The list of loaded proxy files, to avoid running multiple times...
	 * @var array
	 */
	protected $loadedFiles = [];
	
	/**
	 * LazyObjectProxyGenerator constructor.
	 */
	public function __construct() {
		$this->fs = TempFs::makeInstance("lazyObjectProxy");
	}
	
	/**
	 * Returns the singleton instance of this object
	 * @return \LaborDigital\Typo3BetterApi\Container\LazyConstructorInjection\LazyObjectProxyGenerator
	 */
	public static function getInstance(): LazyObjectProxyGenerator {
		if (!empty(static::$self)) return static::$self;
		return static::$self = new static();
	}
	
	/**
	 * Receives the name of an interface to provide as lazy loading proxy.
	 * The proxy class will be generated and cached, before it's name is returned as result of this method.
	 *
	 * @param string $interfaceName
	 *
	 * @return string
	 */
	public function provideProxyForClassSchemaParameter(string $interfaceName): string {
		$proxyClassName = $this->makeProxyClassName($interfaceName);
		$proxyFileName = $this->makeProxyFileName($proxyClassName);
		
		// Check if the file is already loaded
		if (!isset($this->loadedFiles[$proxyFileName])) {
			// Check if we already have the proxy
			$rebuildProxy = TRUE;
			if ($this->fs->hasFile($proxyFileName)) {
				// Read the definition of the file
				$content = $this->fs->getFileContent($proxyFileName);
				preg_match("~__LAZY_PROXY_DEFINITION::(.*?)::__~si", $content, $m);
				$definition = @json_decode($m[1], TRUE);
				if (is_array($definition)) {
					$staleProxy = filemtime($definition["filename"]) != $definition["timestamp"];
					$staleProxy = $staleProxy || $this->fs->getFile($proxyFileName)->getMTime() < $definition["timestamp"];
					if (!$staleProxy) $rebuildProxy = FALSE;
				}
			}
			
			// Build the proxy if required
			if ($rebuildProxy) {
				$src = $this->generateProxyClassSource($interfaceName, $proxyClassName);
				$this->fs->setFileContent($proxyFileName, $src);
			}
			
			// Include the proxy
			$this->fs->includeFile($proxyFileName);
			$this->loadedFiles[$proxyFileName] = TRUE;
		}
		
		// Return the proxy name
		return $proxyClassName;
	}
	
	/**
	 * Generates the proxy config array based on the interface and the proxy class names
	 *
	 * @param string $interfaceName
	 * @param string $proxyClassName
	 *
	 * @return array
	 */
	protected function makeProxyDefinition(string $interfaceName, string $proxyClassName): array {
		// Create the reflection
		$ref = new ReflectionClass($interfaceName);
		return [
			"interface" => $interfaceName,
			"class"     => $proxyClassName,
			"filename"  => $ref->getFileName(),
			"timestamp" => filemtime($ref->getFileName()),
		];
	}
	
	/**
	 * Generates the source code of a single proxy class
	 *
	 * @param string $interfaceName
	 * @param string $proxyClassName
	 *
	 * @return string
	 */
	protected function generateProxyClassSource(string $interfaceName, string $proxyClassName): string {
		// Create the reflection
		$ref = new ReflectionClass($interfaceName);
		
		// Generate the body
		$body = [];
		foreach ($ref->getMethods() as $method) {
			$methodName = $method->getName();
			$returnString = $method->hasReturnType() ? ($method->getReturnType()->getName() === "void" ? "" : "return") : "return";
			$body[] = $this->generateMethodSignature($method) . "{
	$returnString \$this->__call(\"$methodName\", {$this->makeCallArguments($method)});
}";
		}
		
		// Generate "getInstance" method
		$body[] = "public function __getInstance() {
		if(empty(\$this->__instance)) \$this->__instance = \$this->__container->get(static::REAL_CLASS);
		return \$this->__instance;
}";
		
		// Generate the all mighty __call method
		$body[] = "public function __call(\$name, \$arguments) {
		return call_user_func_array([\$this->__getInstance(), \$name], \$arguments);
}";
		// Generate the __get and __set methods
		$body[] = "public function __set(\$name, \$value) {
		\$this->__getInstance()->\$name = \$value;
}";
		$body[] = "public function __get(\$name) {
		return \$this->__getInstance()->\$name;
}";
		
		// Attach the definition to the footer
		$definition = json_encode($this->makeProxyDefinition($interfaceName, $proxyClassName));
		$body[] = "/* __LAZY_PROXY_DEFINITION::$definition::__ */";
		
		// Generate the wrap
		$body = implode(PHP_EOL . PHP_EOL, $body);
		$namespace = Path::classNamespace($proxyClassName);
		$basename = Path::classBasename($proxyClassName);
		return "<?php
namespace $namespace;
use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\LazyLoading\LazyLoadingProxyInterface;
final class $basename implements \\$interfaceName, \LaborDigital\Typo3BetterApi\LazyLoading\LazyLoadingProxyInterface {
	public const REAL_CLASS = \"$interfaceName\";
	protected \$__instance;
	protected \$__container;
	
	public function __construct(TypoContainerInterface \$container){
		\$this->__container = \$container;
	}
	
	$body
}
";
	}
	
	/**
	 * Generates the __call method arguments which are required because func_get_args() does ignore references
	 *
	 * @param \ReflectionMethod $ref
	 *
	 * @return string
	 */
	protected function makeCallArguments(ReflectionMethod $ref): string {
		$params = [];
		foreach ($ref->getParameters() as $parameter)
			$params[] = ($parameter->isPassedByReference() ? "&" : "") . "$" . $parameter->getName();
		return "[" . implode(",", $params) . "]";
	}
	
	/**
	 * Generates the name of the proxy class we want to generate
	 *
	 * @param string $interfaceName
	 *
	 * @return string
	 */
	protected function makeProxyClassName(string $interfaceName): string {
		$baseName = Path::classBasename($interfaceName);
		return "LaborDigital\\Typo3BetterApi\\Container\\LazyConstructorInjection\\Proxy\\" . $baseName . "Proxy";
	}
	
	/**
	 * Generates the cache key for the given proxy class
	 *
	 * @param string $proxyClassName
	 *
	 * @return string
	 */
	protected function makeProxyFileName(string $proxyClassName): string {
		return Inflector::toFile($proxyClassName) . ".php";
	}
}