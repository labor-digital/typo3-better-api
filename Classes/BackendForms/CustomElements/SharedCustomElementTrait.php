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
 * Last modified: 2020.03.19 at 03:00
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\CustomElements;


use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormField;
use LaborDigital\Typo3BetterApi\BackendForms\BackendFormException;
use LaborDigital\Typo3BetterApi\Event\Events\BackendAssetFilterEvent;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\NamingConvention\Naming;
use Neunerlei\Arrays\Arrays;
use Neunerlei\PathUtil\Path;

trait SharedCustomElementTrait {
	
	/**
	 * True if the global event handler for injecting the backend assets is already bound
	 * @var bool
	 */
	protected static $eventBound = FALSE;
	
	/**
	 * The list of javascript files that are registered for the backend
	 * @var array
	 */
	protected static $js = [];
	
	/**
	 * The list of registered css files for the backend
	 * @var array
	 */
	protected static $css = [];
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementContext
	 */
	protected $context;
	
	/**
	 * Internal helper which is used to inject the custom element context for easier access
	 *
	 * @param \LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementContext $context
	 */
	public function __injectContext(CustomElementContext $context) {
		$this->context = $context;
	}
	
	/**
	 * This method is called when, and ONLY IF the field is configured using the AbstractFormField's applyPreset method
	 * It will receive the array of options as well as the field instance. You can use this method to apply additional
	 * TCA configuration to the field, before it is cached for later usage.
	 *
	 * @param AbstractFormField $field   The instance of the field to apply this form element to
	 *                                   The instance will already be preconfigured to be rendered as a custom node in
	 *                                   the form framework
	 * @param array             $options The additional options that were given in the applyPreset method
	 * @param ExtConfigContext  $context The context of the extension, that is currently applying this element
	 *
	 * @return mixed|void
	 */
	public static function configureField(AbstractFormField $field, array $options, ExtConfigContext $context) {
		// Silence
	}
	
	/**
	 * This method can be used to execute some kind of user function that was registered on your tca.
	 *
	 * As an example:
	 *  You have a auto-complete field which should have some entries already in the list.
	 *  You have a class and in it a method which returns you said list of prepared entries.
	 *
	 *  To keep your element clean and agnostic to the data it works with it makes sense to use said class
	 *  as a user function to provide the prepared entry list to your user element.
	 *
	 *  When your custom element is registered using: $table->getField("field")->applyPreset()->customElement($class,
	 *  $options) you can add additional options using the $options attribute. When you pass either a valid
	 *  callback or a typo callback, like my\cool\class->method as "userFunc" you can use this method to load the
	 *  results of the method in your custom element.
	 *
	 *  In your custom element call $this->callUserFunc("userFunc") and you are done... the result will be either the
	 *  result of the registered user function or null ($defaultData)
	 *
	 * @param string $configKey     The key in your field's TCA config that should be searched for
	 * @param array  $arguments     Additional arguments that are passed to the user function
	 * @param null   $defaultData   The default data, which is returned if there was no userFunction registered
	 * @param bool   $allowMultiple By default only a single userFunc is allowed. If you want to register multiple
	 *                              functions, set this to true. Your TCA now supports an array for "userFunc".
	 *
	 *                              Keep in mind tho, that now your user function will always return either
	 *                              $defaultData or the result of the previous user function as the first attribute!
	 *
	 * @return mixed|null
	 * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
	 */
	protected function callUserFunc(string $configKey, array $arguments = [], $defaultData = NULL, bool $allowMultiple = FALSE) {
		// Load the config array
		$config = Arrays::getPath($this->context->getConfig(), "config", []);
		
		// First try the @customOptions, then the field config
		$functionStack = Arrays::getPath($config, ["@customOptions", $configKey], Arrays::getPath($config, [$configKey], []));
		
		// Skip if the stack is empty
		if (empty($functionStack)) return $defaultData;
		
		// Load the function stack from the configuration
		if (is_string($functionStack)) $functionStack = [$functionStack];
		// Make sure we wrap callback arrays in an additional array
		if (is_array($functionStack) && count($functionStack) === 2 && class_exists(reset($functionStack)))
			$functionStack = [$functionStack];
		if (!is_array($functionStack))
			throw new BackendFormException("The configured user function of field: " . $this->context->getFieldName() . " is invalid! Only strings and arrays are allowed!");
		if (!$allowMultiple && count($functionStack) > 1)
			throw new BackendFormException("The configured user function of field: " . $this->context->getFieldName() . " is invalid! Only a single function is allowed!");
		
		// Run the stack
		$result = $defaultData;
		foreach ($functionStack as $func) {
			if (is_string($func)) $func = Naming::typoCallbackToArray($func);
			if (!is_array($func) || count($func) !== 2 && !class_exists(reset($func)))
				throw new BackendFormException("The configured user function of field: " . $this->context->getFieldName() . " is invalid!");
			if (is_array($func) && count($func) === 2 && Arrays::isSequential($func))
				$func = ["class" => $func[0], "method" => $func[1]];
			
			// Validate the class and the method
			if (!class_exists($func["class"]))
				throw new BackendFormException("The configured user function of field: " . $this->context->getFieldName() . " is invalid! The registered class: " . $func["class"] . " does not exist!");
			if (!method_exists($func["class"], $func["method"]))
				throw new BackendFormException("The configured user function of field: " . $this->context->getFieldName() . " is invalid! The registered method: " . $func["method"] . " does not exist!");
			
			// Update first argument when multiple elements are allowed
			if ($allowMultiple) {
				array_unshift($arguments);
				array_unshift($arguments, $result);
			}
			
			// Run the function
			$result = call_user_func_array([$this->context->getInstanceOf($func["class"]), $func["method"]], $arguments);
			
			// Check if we are running multiple
			if (!$allowMultiple) break;
		}
		
		// Done
		return $result;
	}
	
	/**
	 * Can be used to register an additional JS file to the typo3 backend.
	 * The file is only included when the element is rendered.
	 *
	 * @param string $path Either a fully qualified url or a typo path like EXT:...
	 *
	 * @return $this
	 */
	public function registerBackendJs(string $path) {
		$this->addAsset($path);
		return $this;
	}
	
	/**
	 * Can be used to register an additional css file to the typo3 backend.
	 * The file is only included when the element is rendered.
	 *
	 * @param string $path Either a fully qualified url or a typo path like EXT:...
	 *
	 * @return $this
	 */
	public function registerBackendCss(string $path) {
		$this->addAsset($path, TRUE);
		return $this;
	}
	
	/**
	 * Internal helper to append a given js / css path to our stacks
	 * It will check if the file was already added do avoid duplicates
	 *
	 * @param string $path
	 * @param bool   $css
	 */
	protected function addAsset(string $path, bool $css = FALSE) {
		// Make sure our event is bound
		$this->bindEventHandlerIfRequired();
		
		// Helper to resolve urls
		if (!filter_var($path, FILTER_VALIDATE_URL)) {
			$pathAspect = $this->context->TypoContext->getPathAspect();
			$path = $pathAspect->typoPathToRealPath($path);
			$path = Path::makeRelative($path, $pathAspect->getPublicPath());
			if (stripos($path, "./") === 0) $path = "." . $path;
		}
		
		// Add the file to the list or skip
		if ($css) {
			// CSS
			if (in_array($path, static::$css)) return;
			static::$css[] = $path;
		} else {
			// JS
			if (in_array($path, static::$js)) return;
			static::$js[] = $path;
		}
	}
	
	/**
	 * Binds an event handler to inject the backend assets if required
	 */
	protected function bindEventHandlerIfRequired() {
		if (static::$eventBound) return;
		static::$eventBound = TRUE;
		
		// Register event handler to inject the backend assets for this
		$this->context->EventBus->addListener(BackendAssetFilterEvent::class, function (BackendAssetFilterEvent $e) {
			// Register files
			if (!empty(static::$js))
				foreach (static::$js as $file)
					$e->getPageRenderer()->addJsFooterFile($file, "text/javascript", FALSE, FALSE, "", TRUE);
			if (!empty(static::$css))
				foreach (static::$css as $file)
					$e->getPageRenderer()->addCssFile($file, "stylesheet", "all", "", FALSE);
		});
	}
}