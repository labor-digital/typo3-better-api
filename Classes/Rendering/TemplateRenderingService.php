<?php /**
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
 * Last modified: 2020.03.19 at 01:35
 */ /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
/** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace LaborDigital\Typo3BetterApi\Rendering;


use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use LightnCandy\LightnCandy;
use Neunerlei\Arrays\Arrays;
use Neunerlei\FileSystem\Fs;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

class TemplateRenderingService implements SingletonInterface {
	
	/**
	 * The list of mustache renderers
	 * @var \Closure[]
	 */
	protected static $renderers = [];
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs
	 */
	protected $fs;
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
	 */
	protected $container;
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
	 */
	protected $context;
	
	/**
	 * TemplateRenderingService constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface $container
	 * @param \LaborDigital\Typo3BetterApi\TypoContext\TypoContext          $context
	 */
	public function __construct(TypoContainerInterface $container, TypoContext $context) {
		$this->container = $container;
		$this->context = $context;
		$this->fs = TempFs::makeInstance("templateRendering");
	}
	
	/**
	 * This method allows you to render a mustache or handlebars template into a string.
	 * As engine we use LightnCandy internally, but we use typo3's caching framework to store the compiled templates,
	 * for a faster execution of the same templates.
	 *
	 * @param string $template Either a mustache template as string, or a path like FILE:EXT:...
	 * @param array  $data     The view data to use for the renderer object
	 * @param array  $options  $options LightnCandy compile time- and run time options
	 *
	 * @return string
	 * @see https://packagist.org/packages/zordius/lightncandy
	 * @see https://zordius.github.io/HandlebarsCookbook/0003-hello.html
	 */
	public function renderMustache(string $template, array $data = [], array $options = []): string {
		
		// Check if we have to load a template file
		if (substr(strtolower($template), 0, 5) === "file:") {
			$templateFile = $this->context->getPathAspect()->typoPathToRealPath($template);
			$template = Fs::readFile($templateFile);
		}
		
		// Check if we already know the renderer
		$templateFile = "template-" . md5($template) . ".php";
		if (isset(static::$renderers[$templateFile]))
			$renderer = static::$renderers[$templateFile];
		else {
			// Check if we have to compile the template
			if (!$this->fs->hasFile($templateFile)) {
				if (!isset($options["flags"])) $options["flags"] = LightnCandy::FLAG_BESTPERFORMANCE ^ LightnCandy::FLAG_ERROR_EXCEPTION ^ LightnCandy::FLAG_PARENT ^ LightnCandy::FLAG_RUNTIMEPARTIAL;
				$php = LightnCandy::compile($template, $this->injectMustacheViewHelpers($options));
				if (substr(trim($php), 0, 5) !== "<?php") $php = "<?php" . PHP_EOL . $php;
				$this->fs->setFileContent($templateFile, $php);
			}
			
			// Load the renderer from the compiled template
			$renderer = static::$renderers[$templateFile] = $this->fs->includeFile($templateFile);
		}
		
		// Execute the renderer
		return $renderer($data);
	}
	
	/**
	 * Returns a fluid template view instance.
	 *
	 * @param string $templateName Either a full template file name or a file path as EXT:.../template.html, or a file
	 *                             that is relative to the given "templateRootPaths"
	 * @param array  $options      Additional configuration options
	 *                             - templateRootPaths array: If no full template path is given as template name this
	 *                             should be an array of template root path's to look for your template
	 *                             - partialRootPaths: array: Can be used to set the partial root paths of the template
	 *                             - layoutRootPaths: array: Can be used to set the layout root paths of the template
	 *                             - format string (html): Defines the file type of the template to handle.
	 *
	 * @return \TYPO3\CMS\Fluid\View\StandaloneView
	 */
	public function getFluidView(string $templateName, array $options = []): StandaloneView {
		// Prepare the options
		$options = Options::make($options, [
			"templateRootPaths" => [
				"type"    => "array",
				"default" => [],
			],
			"partialRootPaths"  => [
				"type"    => "array",
				"default" => [],
			],
			"layoutRootPaths"   => [
				"type"    => "array",
				"default" => [],
			],
			"format"            => [
				"type"    => "string",
				"default" => "html",
			],
		]);
		
		// Prepare the template name
		$filename = $this->context->getPathAspect()->typoPathToRealPath($templateName);
		if (stripos(substr($filename, -6), ".") === FALSE) $filename .= ".html";
		
		// Build the instance
		$instance = $this->container->get(StandaloneView::class);
		$instance->setFormat($options["format"]);
		if (!empty($options["templateRootPaths"])) $instance->setTemplateRootPaths($options["templateRootPaths"]);
		if (!empty($options["partialRootPaths"])) $instance->setPartialRootPaths($options["partialRootPaths"]);
		if (!empty($options["layoutRootPaths"])) $instance->setLayoutRootPaths($options["layoutRootPaths"]);
		empty($options["templateRootPaths"]) ? $instance->setTemplatePathAndFilename($filename) : $instance->setTemplate($templateName);
		
		// Done
		return $instance;
	}
	
	/**
	 * Internal helper to inject some quite useful viewhelpers into the mustache landscape...
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	protected function injectMustacheViewHelpers(array $options) {
		// Merge in our custom helpers
		return Arrays::merge([
			"helpers" => [
				"translate" => function ($selector) {
					if (!is_string($selector)) return "";
					return \LaborDigital\Typo3BetterApi\Container\TypoContainer::getInstance()
						->get(\LaborDigital\Typo3BetterApi\Translation\TranslationService::class)->translateMaybe($selector);
				},
			],
		], $options);
	}
}