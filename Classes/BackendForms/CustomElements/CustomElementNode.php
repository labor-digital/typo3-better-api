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
 * Last modified: 2020.03.19 at 02:59
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\CustomElements;


use LaborDigital\Typo3BetterApi\BackendForms\BackendFormException;
use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use LaborDigital\Typo3BetterApi\Event\Events\BackendFormCustomElementPostProcessorEvent;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\MathUtility;

class CustomElementNode extends AbstractFormElement {
	use CommonServiceLocatorTrait;
	
	/**
	 * Holds the current context to be able to update our local data if required
	 * @var \LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementContext
	 */
	protected $context;
	
	/**
	 * @inheritDoc
	 */
	public function render() {
		// Create a new context
		$this->context = $this->getInstanceOf(CustomElementContext::class, [
			[
				"rawData"           => $this->data,
				"iconFactory"       => $this->iconFactory,
				"rootNode"          => $this,
				"defaultInputWidth" => $this->defaultInputWidth,
				"maxInputWidth"     => $this->maxInputWidth,
				"minInputWidth"     => $this->minimumInputWidth,
			],
		]);
		
		// Validate if the class exists
		$customElementClass = $this->context->getElementClass();
		if (!class_exists($customElementClass))
			throw new BackendFormException("Could not render your field: " . $this->context->getFieldName() . " to use the custom element with class: $customElementClass. Because the class does not exist!");
		if (!in_array(CustomElementInterface::class, class_implements($customElementClass)))
			throw new BackendFormException("Could not render your field: " . $this->context->getFieldName() . " to use the custom element with class: $customElementClass. Because the class does not implement the required " . CustomElementInterface::class . " interface!");
		
		// Create the instance to render
		/** @var \LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementInterface $i */
		$i = $this->getInstanceOf($customElementClass);
		if ($i instanceof AbstractCustomElement) $i->__injectContext($this->context);
		
		// Initialize the result
		$result = $this->initializeResultArray();
		$result["html"] = $i->render($this->context);
		
		// Update the data of this node
		$this->__refreshData();
		
		// Check if we can render the field wizard
		$fieldWizardResult = ["html" => ""];
		if (method_exists($this, "renderFieldWizard")) {
			$fieldWizardResult = $this->renderFieldWizard();
			$result = $this->mergeChildReturnIntoExistingResult($result, $fieldWizardResult, FALSE);
		}
		
		// Check if we should apply the outer wrap
		if ($this->context->isApplyOuterWrap()) {
			// Load the field TCA's config sub array
			$config = Arrays::getPath($this->context->getConfig(), ["config"], []);
			
			// Calculate field size
			$size = Arrays::getPath($config, ["size"], $this->context->getDefaultInputWidth());
			$size = MathUtility::forceIntegerInRange($size, $this->context->getMinInputWidth(), $this->context->getMaxInputWidth());
			$width = (int)$this->context->getRootNode()->__callMethod("formMaxWidth", [$size]);
			$html = $result["html"];
			$wizardHtml = $fieldWizardResult["html"];
			$result["html"] = <<<HTML
				<div class="form-control-wrap" style="max-width: $width px;">
					<div class="form-wizards-wrap">
						<div class="form-wizards-element">
							$html
						</div>
						<div class="form-wizards-items-bottom">
							$wizardHtml
						</div>
					</div>
				</div>
HTML;
		}
		
		// Filter the result
		$result = $i->filterResultArray($result);
		
		// Allow the outside world to filter the result
		TypoEventBus::getInstance()->dispatch(($e = new BackendFormCustomElementPostProcessorEvent($this->context, $result)));
		return $e->getResult();
	}
	
	/**
	 * Internal helper to allow the external world access to all protected methods of this object
	 *
	 * @param string $method
	 * @param array  $args
	 *
	 * @return mixed
	 * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
	 */
	public function __callMethod(string $method, array $args = []) {
		// Check if the method exists
		if (!method_exists($this, $method))
			throw new BackendFormException("It is not allowed to call method: $method on the root node, because the method does not exist!");
		// Make sure our data is up to data
		$this->__refreshData();
		// Call the method
		return call_user_func_array([$this, $method], $args);
	}
	
	/**
	 * The companion to __callMethod. Checks if a certain, protected method exists or not
	 *
	 * @param string $method
	 *
	 * @return bool
	 */
	public function __hasMethod(string $method): bool {
		return method_exists($this, $method);
	}
	
	/**
	 * Internal helper to synchronize the context's data back to this nodes data storage
	 */
	public function __refreshData() {
		$this->data = $this->context->getRawData();
	}
}