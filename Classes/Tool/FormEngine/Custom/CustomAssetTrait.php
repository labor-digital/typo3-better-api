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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\FormEngine\Custom;


use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use Neunerlei\PathUtil\Path;

trait CustomAssetTrait
{
    /** @deprecated will be removed in v11 */
    protected static $eventBound = false;
    /** @deprecated will be removed in v11 */
    protected static $js = [];
    /** @deprecated will be removed in v11 */
    protected static $css = [];
    
    /**
     * The list of requireJs modules that must be appended for our collected assets
     *
     * @var array
     */
    protected $requireJsModules = [];
    
    /**
     * Can be used to register an additional JS file to the typo3 backend.
     * The file is only included when the element is rendered.
     *
     * @param   string  $path  Either a fully qualified url or a typo path like EXT:...
     *
     * @return $this
     */
    public function registerScript(string $path): self
    {
        return $this->addAsset($path);
    }
    
    /**
     * Can be used to register an additional css file to the typo3 backend.
     * The file is only included when the element is rendered.
     *
     * @param   string  $path  Either a fully qualified url or a typo path like EXT:...
     *
     * @return $this
     */
    public function registerStylesheet(string $path): self
    {
        return $this->addAsset($path, true);
    }
    
    /**
     * Allows you to register some inline javascript code which will be executed when the form element was loaded
     *
     * @param   string  $code
     *
     * @return $this
     */
    public function registerInlineJs(string $code): self
    {
        return $this->registerRequireJsModule('TYPO3/CMS/Backend/FormEngine', $code);
    }
    
    /**
     * Registers a new require js module to be loaded for this form element.
     *
     * @param   string       $moduleName        The name of the module to load (e.g. TYPO3/CMS/FooBar/MyMagicModule)
     * @param   string|null  $callbackFunction  An optional callback function which will be executed after your
     *                                          module has been loaded. If you define a function, the function
     *                                          will receive the module as argument, which allows you to
     *                                          create dynamic factories for single form elements.
     *                                          e.g. function(module){ new module()}
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/JavaScript/RequireJS/Extensions/Index.html
     */
    public function registerRequireJsModule(string $moduleName, ?string $callbackFunction = null): self
    {
        if (is_string($callbackFunction)) {
            $code = trim(trim($callbackFunction), '; ');
            if (! str_starts_with($code, 'function')) {
                $code = 'function(module){' . $code . ';}';
            }
        } else {
            $code = null;
        }
        
        $this->requireJsModules[] = [$moduleName => $code];
        
        return $this;
    }
    
    /**
     * Returns all registered requireJS modules to load for the current element
     *
     * @return array
     */
    public function getRequireJsModules(): array
    {
        return $this->requireJsModules;
    }
    
    /**
     * Internal helper that resolves js and css paths and creates requireJs wrappers for them.
     * This allows us to side-load all kinds of assets even if inline forms are used.
     * The javascript automatically de-duplicates requests based on the given path
     *
     * @param   string  $path
     * @param   bool    $css
     *
     * @return $this
     */
    protected function addAsset(string $path, bool $css = false): self
    {
        /** @noinspection BypassedUrlValidationInspection */
        if (! filter_var($path, FILTER_VALIDATE_URL)) {
            $pathAspect = TypoContext::getInstance()->path();
            $path = $pathAspect->typoPathToRealPath($path);
            $path = Path::makeRelative($path, $pathAspect->getPublicPath());
            
            if (! empty($path) && ! in_array($path[0], ['/', '.'], true)) {
                $path = '/' . $path;
            }
        }
        
        $hash = md5($path);
        $code = 'function(){var prop=\'T3BA_FEASREG\', hash=\'' . $hash . '\';' .
                'if(!window[prop])window[prop]=[];' .
                'if(window[prop].indexOf(hash)!==-1)return;' .
                'window[prop].push(hash);';
        
        if ($css) {
            $code .= 'var el=document.createElement(\'link\');' .
                     'el.type=\'text/css\';' .
                     'el.rel=\'stylesheet\';' .
                     'el.href=\'' . $path . '\';';
        } else {
            $code .= 'var el=document.createElement(\'script\');' .
                     'el.src=\'' . $path . '\';';
        }
        
        $code .= 'document.getElementsByTagName(\'head\')[0].appendChild(el);}';
        
        $this->registerInlineJs($code);
        
        return $this;
    }
    
    /**
     * @deprecated will be removed in v11
     */
    protected function bindEventHandlerIfRequired(): void
    {
    }
}
