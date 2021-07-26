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
 * Last modified: 2021.07.26 at 15:32
 */

declare(strict_types=1);
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
 * Last modified: 2020.03.19 at 01:35
 */

namespace LaborDigital\T3ba\Tool\Rendering;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\VarFs\Mount;
use LaborDigital\T3ba\Core\VarFs\VarFs;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;
use LightnCandy\LightnCandy;
use Neunerlei\Arrays\Arrays;
use Neunerlei\FileSystem\Fs;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

class TemplateRenderingService implements SingletonInterface
{
    use ContainerAwareTrait;
    use TypoContextAwareTrait;
    
    /**
     * The list of mustache renderers
     *
     * @var \Closure[]
     */
    protected static $renderers = [];
    
    /**
     * @var Mount
     */
    protected $fsMount;
    
    /**
     * TemplateRenderingService constructor.
     *
     * @param   \LaborDigital\T3ba\Core\VarFs\VarFs  $fs
     */
    public function __construct(VarFs $fs)
    {
        $this->fsMount = $fs->getMount('TemplateRendering');
    }
    
    /**
     * This method allows you to render a mustache or handlebars template into a string.
     * As engine we use LightNCandy internally with compiled templates for a faster execution of the same variants.
     *
     * @param   string  $template  Either a mustache template as string, or a path like FILE:EXT:...
     * @param   array   $data      The view data to use for the renderer object
     * @param   array   $options   $options LightNCandy compile time- and run time options
     *
     * @return string
     * @see https://packagist.org/packages/zordius/lightncandy
     * @see https://zordius.github.io/HandlebarsCookbook/0003-hello.html
     */
    public function renderMustache(string $template, array $data = [], array $options = []): string
    {
        // Check if we have to load a template file
        if (stripos($template, 'file:') === 0) {
            $templateFile = $this->getTypoContext()->path()->typoPathToRealPath($template);
            $template = Fs::readFile($templateFile);
        }
        
        // Check if we already know the renderer
        $templateFile = 'template-' . md5($template) . '.php';
        if (isset(static::$renderers[$templateFile])) {
            $renderer = static::$renderers[$templateFile];
        } else {
            // Check if we have to compile the template
            if (! $this->fsMount->hasFile($templateFile)) {
                if (! isset($options['flags'])) {
                    $options['flags'] = LightnCandy::FLAG_BESTPERFORMANCE ^ LightnCandy::FLAG_ERROR_EXCEPTION
                                        ^ LightnCandy::FLAG_PARENT ^ LightnCandy::FLAG_RUNTIMEPARTIAL;
                }
                
                $php = LightnCandy::compile($template, $this->injectMustacheViewHelpers($options));
                
                if (strpos(trim($php), '<?php') !== 0) {
                    $php = '<?php' . PHP_EOL . $php;
                }
                
                $this->fsMount->setFileContent($templateFile, $php);
            }
            
            // Load the renderer from the compiled template
            $renderer = static::$renderers[$templateFile] = $this->fsMount->includeFile($templateFile);
        }
        
        // Execute the renderer
        return $renderer($data);
    }
    
    /**
     * Returns a fluid template view instance.
     *
     * @param   string  $templateName  Either a full template file name or a file path as EXT:.../template.html, or a
     *                                 file that is relative to the given "templateRootPaths"
     * @param   array   $options       Additional configuration options
     *                                 - templateRootPaths array: If no full template path is given as template name
     *                                 this should be an array of template root path's to look for your template
     *                                 - partialRootPaths: array: Can be used to set the partial root paths of the
     *                                 template
     *                                 - layoutRootPaths: array: Can be used to set the layout root paths of the
     *                                 template
     *                                 - format string (html): Defines the file type of the template to handle.
     *
     * @return \TYPO3\CMS\Fluid\View\StandaloneView
     */
    public function getFluidView(string $templateName, array $options = []): StandaloneView
    {
        // Prepare the options
        $options = Options::make($options, [
            'templateRootPaths' => [
                'type' => 'array',
                'default' => [],
            ],
            'partialRootPaths' => [
                'type' => 'array',
                'default' => [],
            ],
            'layoutRootPaths' => [
                'type' => 'array',
                'default' => [],
            ],
            'format' => [
                'type' => 'string',
                'default' => 'html',
            ],
        ]);
        
        // Prepare the template name
        $filename = $this->getTypoContext()->path()->typoPathToRealPath($templateName);
        if (strpos(substr($filename, -6), '.') === false) {
            $filename .= '.html';
        }
        
        // Build the instance
        $instance = $this->makeInstance(StandaloneView::class);
        $instance->setFormat($options['format']);
        
        if (! empty($options['templateRootPaths'])) {
            $instance->setTemplateRootPaths($options['templateRootPaths']);
        }
        
        if (! empty($options['partialRootPaths'])) {
            $instance->setPartialRootPaths($options['partialRootPaths']);
        }
        
        if (! empty($options['layoutRootPaths'])) {
            $instance->setLayoutRootPaths($options['layoutRootPaths']);
        }
        
        empty($options['templateRootPaths']) ? $instance->setTemplatePathAndFilename($filename)
            : $instance->setTemplate($templateName);
        
        // Done
        return $instance;
    }
    
    /**
     * Renders a standalone fluid source code as an html string
     *
     * @param   string  $source   Either a mustache template as string, or a path like FILE:EXT:...
     * @param   array   $data     The view data to use for the renderer object
     * @param   array   $options  {@link getFluidView() for the possible options}
     *
     * @return string
     */
    public function renderFluid(string $source, array $data = [], array $options = []): string
    {
        if (stripos($source, 'file:') === 0) {
            $view = $this->getFluidView($source, $options);
        } else {
            $view = $this->getFluidView('', $options);
            $view->setTemplateSource($source);
        }
        
        $view->assignMultiple($data);
        
        return $view->render();
    }
    
    /**
     * Internal helper to inject some quite useful view helpers into the mustache landscape...
     *
     * @param   array  $options
     *
     * @return array
     * @noinspection PhpFullyQualifiedNameUsageInspection
     * @noinspection StaticClosureCanBeUsedInspection
     */
    protected function injectMustacheViewHelpers(array $options): array
    {
        // Merge in our custom helpers
        return Arrays::merge([
            'helpers' => [
                'translate' => function ($selector) {
                    if (! is_string($selector)) {
                        return '';
                    }
                    
                    return \LaborDigital\T3ba\Tool\TypoContext\TypoContext
                        ::getInstance()->di()->cs()->translator->translate($selector);
                },
            ],
        ], $options);
    }
}
