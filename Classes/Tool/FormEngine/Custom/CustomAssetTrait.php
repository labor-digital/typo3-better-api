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


use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Event\Backend\BackendAssetFilterEvent;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use Neunerlei\PathUtil\Path;

trait CustomAssetTrait
{
    
    /**
     * True if the global event handler for injecting the backend assets is already bound
     *
     * @var bool
     */
    protected static $eventBound = false;
    
    /**
     * The list of javascript files that are registered for the backend
     *
     * @var array
     */
    protected static $js = [];
    
    /**
     * The list of registered css files for the backend
     *
     * @var array
     */
    protected static $css = [];
    
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
     * Internal helper to append a given js / css path to our stacks
     * It will check if the file was already added do avoid duplicates
     *
     * @param   string  $path
     * @param   bool    $css
     *
     * @return $this
     */
    protected function addAsset(string $path, bool $css = false): self
    {
        // Make sure our event is bound
        $this->bindEventHandlerIfRequired();
        
        // Helper to resolve urls
        /** @noinspection BypassedUrlValidationInspection */
        if (! filter_var($path, FILTER_VALIDATE_URL)) {
            $pathAspect = TypoContext::getInstance()->path();
            $path = $pathAspect->typoPathToRealPath($path);
            $path = Path::makeRelative($path, $pathAspect->getPublicPath());
            
            if (! empty($path) && ! in_array($path[0], ['/', '.'], true)) {
                $path = '/' . $path;
            }
        }
        
        // Add the file to the list or skip
        if ($css) {
            // CSS
            if (in_array($path, static::$css, true)) {
                return $this;
            }
            static::$css[] = $path;
        } else {
            // JS
            if (in_array($path, static::$js, true)) {
                return $this;
            }
            static::$js[] = $path;
        }
        
        return $this;
    }
    
    /**
     * Binds an event handler to inject the backend assets if required
     */
    protected function bindEventHandlerIfRequired(): void
    {
        if (static::$eventBound) {
            return;
        }
        
        static::$eventBound = true;
        
        TypoEventBus::getInstance()->addListener(BackendAssetFilterEvent::class, function (BackendAssetFilterEvent $e) {
            $renderer = $e->getPageRenderer();
            if (! empty(static::$js)) {
                foreach (static::$js as $file) {
                    $renderer->addJsFooterFile($file, 'text/javascript', false, false, '', true);
                }
            }
            
            if (! empty(static::$css)) {
                foreach (static::$css as $file) {
                    $renderer->addCssFile($file, 'stylesheet', 'all', '', false);
                }
            }
        });
    }
}
