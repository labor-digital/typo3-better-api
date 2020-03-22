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
 * Last modified: 2020.03.19 at 02:52
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\CustomElements;


use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractCustomElement implements CustomElementInterface {
	use SharedCustomElementTrait;
	
	/**
	 * @inheritDoc
	 */
	public function filterResultArray(array $result): array {
		// Silence
		return $result;
	}
	
	/**
	 * @inheritDoc
	 */
	public function backendSaveFilter(CustomElementFormActionContext $context) {
		// Silence
	}
	
	/**
	 * @inheritDoc
	 */
	public function backendActionHandler(CustomElementFormActionContext $context) {
		// Silence
	}
	
	/**
	 * @inheritDoc
	 */
	public function backendFormFilter(CustomElementFormActionContext $context) {
		// Silence
	}
	
	/**
	 * Your input field, like your custom <input type="text"...> has to have some quite
	 * specific and extensive attributes in order to work correctly with the form engine of typo3's backend.
	 *
	 * This method will return the complete string of those attributes so you can apply them into your template
	 * without thinking about most of the internals.
	 *
	 * ATTENTION: This helper sets the ID and the CLASS html attributes. If you want to change or add, lets say
	 * a class use the $mergeAttributes like ["class" => ["myClass"]] to supply your additional class to the built
	 * output. You can't specify the class attribute twice, that will not be parsed correctly by the browser!
	 *
	 * @param array $mergeAttributes
	 *
	 * @return string
	 */
	protected function getInputAttributes(array $mergeAttributes = []): string {
		// Load the field TCA's config sub array
		$config = Arrays::getPath($this->context->getConfig(), ["config"], []);
		// Build required attribute values
		$jsonValidation = $this->context->getRootNode()->__callMethod("getValidationDataAsJsonString", [$config]);
		$evalList = implode(",", array_unique(Arrays::makeFromStringList(Arrays::getPath($config, "eval", ""))));
		$isIn = trim(Arrays::getPath($config, "is_in", ""));
		
		// Build default attributes
		$attributes = [
			'id'                               => $this->context->getRenderId(),
			'class'                            => implode(' ', [
				'form-control',
				't3js-clearable',
				'hasDefaultValue',
			]),
			'data-formengine-validation-rules' => $jsonValidation,
			'data-formengine-input-params'     => json_encode([
				'field'    => $this->context->getRenderName(),
				'evalList' => $evalList,
				'is_in'    => $isIn,
			]),
			'data-formengine-input-name'       => $this->context->getRenderName(),
		];
		
		// Merge and implode attributes
		$attributes = Arrays::merge($attributes, $mergeAttributes);
		return GeneralUtility::implodeAttributes($attributes, TRUE);
	}
	
	/**
	 * Similar to your input field, the hidden field, which stores the real data
	 * also has to have quite specific attributes. This method returns those attributes in the
	 * same manner as getInputAttributes().
	 *
	 * It sets the NAME and the VALUE attribute by default.
	 *
	 * @param array $mergeAttributes
	 *
	 * @return string
	 */
	protected function getHiddenAttributes(array $mergeAttributes = []): string {
		// Make sure we are not breaking the backend
		$value = $this->context->getValue();
		if (is_array($value))
			if (count($value) === 1) $value = reset($value);
			else $value = implode(", ", $value);
		
		// Build default attributes
		$attributes = [
			"name"  => $this->context->getRenderName(),
			"value" => $value,
		];
		
		// Merge and implode attributes
		$attributes = Arrays::merge($attributes, $mergeAttributes);
		return GeneralUtility::implodeAttributes($attributes, TRUE);
	}
	
	/**
	 * It's not enough to add a new value field like <input type="text"...> to your template.
	 * You will also have to add an additional <input type="hidden"...> field which is used as a data-holder
	 * by typo3's form engine.
	 *
	 * To save you most of the hazel you may use this method to get the already prepared html for said hidden field.
	 *
	 * Also see getHiddenAttributes() for how to adjust the attributes of the generated input
	 *
	 * @param array $mergeAttributes
	 *
	 * @return string
	 */
	protected function getHiddenHtml(array $mergeAttributes = []): string {
		$attr = $this->getHiddenAttributes($mergeAttributes);
		return "<input type=\"hidden\" $attr />";
	}
	
	/**
	 * This method is a shortcut to $this->template->renderMustache() and as you might have guessed
	 * will render a mustache template you will supply. See TemplateRenderingService::renderMustache() for the options.
	 *
	 * In addition to being a handy shortcut, this method comes with a lot of variables already pre-configured.
	 * When you use this method to render your template you may use in addition to everything you supply using $data:
	 *  - {{value}} Renders the current field's value
	 *  - {{{inputAttributes}}} The html attributes for the real input field (mind the 3 curly braces)
	 *  - {{renderId}} The id of your input field, if you don't want to use inputAttributes
	 *  - {{renderName}} The name of your hidden field to hold the data
	 *  - {{{hiddenField}}} The preconfigured hidden field to hold your data (mind the 3 curly braces)
	 *  - {{{hiddenAttributes}}} The html attributes for the hidden input field (mind the 3 curly braces)
	 *
	 * @param string $template
	 * @param array  $data
	 * @param array  $options
	 *
	 * @return string
	 *
	 * @see \LaborDigital\Typo3BetterApi\Rendering\TemplateRenderingService::renderMustache()
	 */
	protected function renderTemplate(string $template, array $data = [], array $options = []): string {
		if (!isset($data["value"])) $data["value"] = $this->context->getValue();
		if (!isset($data["inputAttributes"])) $data["inputAttributes"] = $this->getInputAttributes();
		if (!isset($data["renderId"])) $data["renderId"] = $this->context->getRenderId();
		if (!isset($data["renderName"])) $data["renderName"] = $this->context->getRenderName();
		if (!isset($data["hiddenField"])) $data["hiddenField"] = $this->getHiddenHtml();
		if (!isset($data["hiddenAttributes"])) $data["hiddenAttributes"] = $this->getHiddenAttributes();
		return $this->context->TemplateRendering->renderMustache($template, $data, $options);
	}
}