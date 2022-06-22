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


namespace LaborDigital\T3ba\ExtConfigHandler\Common\Assets;

use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigConfigurator;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\Options\Options;

/**
 * Trait AssetCollectorTrait
 *
 * MUST BE Applied to a class that extends AbstractExtConfigConfigurator
 *
 * @package LaborDigital\T3ba\ExtConfigHandler\Common\Assets
 * @see     AbstractExtConfigConfigurator
 */
trait AssetCollectorTrait
{
    protected $assetActionList = [];
    
    /**
     * Registers a new js file to be rendered on the page
     *
     * NOTE: To keep the method in sync with the AssetCollector, it starts with "add" not with "register"
     *
     * @param   string  $identifier  A unique identifier that allows
     * @param   string  $source      Use something like EXT:ext_key/Resources/Public/Scripts/script.js
     *                               You can use fully qualified urls as well.
     * @param   array   $options     Additional options to apply to the script tag
     *                               - arguments array: Additional HTML attributes to add to the "script" tag.
     *                               - priority bool (FALSE): If this flag is set to true, the script will be rendered
     *                               in the "head" tag, otherwise at the footer of the page.
     *
     * @return $this
     */
    public function addJavaScript(string $identifier, string $source, array $options = [])
    {
        return $this->addAdderAssetDefinition(__FUNCTION__, $identifier, $source, $options);
    }
    
    /**
     * Alias of addJavaScript()
     *
     * @param   string  $identifier  A unique identifier that allows
     * @param   string  $source      Use something like EXT:ext_key/Resources/Public/Scripts/script.js
     *                               You can use fully qualified urls as well.
     * @param   array   $options     Additional options to apply to the script tag
     *                               - arguments array: Additional HTML attributes to add to the "script" tag.
     *                               - priority bool (FALSE): If this flag is set to true, the script will be rendered
     *                               in the "head" tag, otherwise at the footer of the page.
     *
     * @return $this
     */
    public function registerJavaScript(string $identifier, string $source, array $options = [])
    {
        return $this->addJavaScript($identifier, $source, $options);
    }
    
    /**
     * Registers the given javascript code to be rendered on the page
     *
     * NOTE: To keep the method in sync with the AssetCollector, it starts with "add" not with "register"
     *
     * @param   string  $identifier  A unique identifier that allows
     * @param   string  $source      Javascript code to be rendered
     * @param   array   $options     Additional options to apply to the script tag
     *                               - arguments array: Additional HTML attributes to add to the "script" tag.
     *                               - priority bool (FALSE): If this flag is set to true, the script will be rendered
     *                               in the "head" tag, otherwise at the footer of the page.
     *
     * @return $this
     */
    public function addInlineJavaScript(string $identifier, string $source, array $options = [])
    {
        return $this->addAdderAssetDefinition(__FUNCTION__, $identifier, $source, $options);
    }
    
    /**
     * Alias of addInlineJavaScript
     *
     * @param   string  $identifier  A unique identifier that allows
     * @param   string  $source      Javascript code to be rendered
     * @param   array   $options     Additional options to apply to the script tag
     *                               - arguments array: Additional HTML attributes to add to the "script" tag.
     *                               - priority bool (FALSE): If this flag is set to true, the script will be rendered
     *                               in the "head" tag, otherwise at the footer of the page.
     *
     * @return $this
     */
    public function registerInlineJavaScript(string $identifier, string $source, array $options = [])
    {
        return $this->addInlineJavaScript($identifier, $source, $options);
    }
    
    /**
     * Registers a new style file to be rendered on the page
     *
     * NOTE: To keep the method in sync with the AssetCollector, it starts with "add" not with "register"
     *
     * @param   string  $identifier  A unique identifier that allows
     * @param   string  $source      Use something like EXT:ext_key/Resources/Public/Styles/style.css
     *                               You can use fully qualified urls as well.
     * @param   array   $options     Additional options to apply to the style tag
     *                               - arguments array: Additional HTML attributes to add to the "style" tag.
     *                               - priority bool (FALSE): If this flag is set to true, the style will be rendered
     *                               in the "head" tag, otherwise at the footer of the page.
     *
     * @return $this
     */
    public function addStyleSheet(string $identifier, string $source, array $options = [])
    {
        return $this->addAdderAssetDefinition(__FUNCTION__, $identifier, $source, $options);
    }
    
    /**
     * Alias of addStyleSheet()
     *
     * @param   string  $identifier  A unique identifier that allows
     * @param   string  $source      Use something like EXT:ext_key/Resources/Public/Styles/style.css
     *                               You can use fully qualified urls as well.
     * @param   array   $options     Additional options to apply to the style tag
     *                               - arguments array: Additional HTML attributes to add to the "style" tag.
     *                               - priority bool (FALSE): If this flag is set to true, the style will be rendered
     *                               in the "head" tag, otherwise at the footer of the page.
     *
     * @return $this
     */
    public function registerStyleSheet(string $identifier, string $source, array $options = [])
    {
        return $this->addStyleSheet($identifier, $source, $options);
    }
    
    /**
     * Registers a new style file to be rendered on the page
     *
     * NOTE: To keep the method in sync with the AssetCollector, it starts with "add" not with "register"
     *
     * @param   string  $identifier  A unique identifier that allows
     * @param   string  $source      CSS code to be rendered
     * @param   array   $options     Additional options to apply to the style tag
     *                               - arguments array: Additional HTML attributes to add to the "style" tag.
     *                               - priority bool (FALSE): If this flag is set to true, the style will be rendered
     *                               in the "head" tag, otherwise at the footer of the page.
     *
     * @return $this
     */
    public function addInlineStyleSheet(string $identifier, string $source, array $options = [])
    {
        return $this->addAdderAssetDefinition(__FUNCTION__, $identifier, $source, $options);
    }
    
    /**
     * Alias of addInlineStyleSheet()
     *
     * @param   string  $identifier  A unique identifier that allows
     * @param   string  $source      CSS code to be rendered
     * @param   array   $options     Additional options to apply to the style tag
     *                               - arguments array: Additional HTML attributes to add to the "style" tag.
     *                               - priority bool (FALSE): If this flag is set to true, the style will be rendered
     *                               in the "head" tag, otherwise at the footer of the page.
     *
     * @return $this
     */
    public function registerInlineStyleSheet(string $identifier, string $source, array $options = [])
    {
        return $this->addInlineStyleSheet($identifier, $source, $options);
    }
    
    /**
     * Removes a previously registered javascript file from the list.
     * Note: This works even on scripts registered through a viewHelper!
     *
     * @param   string  $identifier
     *
     * @return $this
     */
    public function removeJavaScript(string $identifier)
    {
        $this->assetActionList[] = [__FUNCTION__, [$identifier]];
        
        return $this;
    }
    
    /**
     * Removes a previously registered javascript code from the list.
     * Note: This works even on scripts registered through a viewHelper!
     *
     * @param   string  $identifier
     *
     * @return $this
     */
    public function removeInlineJavaScript(string $identifier): self
    {
        $this->assetActionList[] = [__FUNCTION__, [$identifier]];
        
        return $this;
    }
    
    /**
     * Removes a previously registered stylesheet file from the list.
     * Note: This works even on stylesheets registered through a viewHelper!
     *
     * @param   string  $identifier
     *
     * @return $this
     */
    public function removeStyleSheet(string $identifier): self
    {
        $this->assetActionList[] = [__FUNCTION__, [$identifier]];
        
        return $this;
    }
    
    /**
     * Removes a previously registered css code from the list.
     * Note: This works even on css registered through a viewHelper!
     *
     * @param   string  $identifier
     *
     * @return $this
     */
    public function removeInlineStyleSheet(string $identifier): self
    {
        $this->assetActionList[] = [__FUNCTION__, [$identifier]];
        
        return $this;
    }
    
    /**
     * Internal helper to add a new asset action to the action list
     *
     * @param   string  $function
     * @param   string  $identifier
     * @param   string  $source
     * @param   array   $options
     *
     * @return $this
     */
    protected function addAdderAssetDefinition(string $function, string $identifier, string $source, array $options = [])
    {
        $options = Options::make($options, [
            'arguments' => [
                'type' => 'array',
                'default' => [],
            ],
            'priority' => [
                'type' => 'bool',
                'default' => false,
            ],
        ]);
        
        // GeneralUtility::createVersionNumberedFilename is unreliable for the backend,
        // therefore this fix was implemented to add a timestamp automatically.
        [$path, $query] = explode('?', $source);
        $path = $this->context->resolveFilename($path, false);
        if (file_exists($path)) {
            $source .= (empty($query) ? '?' : '&') . filemtime($path);
        }
        
        /** @var AbstractExtConfigConfigurator|AssetCollectorTrait $this */
        $this->assetActionList[] = [
            $function,
            $this->context->replaceMarkers(
                [
                    $identifier,
                    $this->context->resolveFilename($source),
                    $options['arguments'],
                    ['priority' => $options['priority']],
                ]
            ),
        ];
        
        return $this;
    }
    
    /**
     * Stores the collected asset actions into the current config state as "assets".
     * You can always use namespaces to modify the final storage location
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $state
     */
    protected function storeAssetCollectorConfiguration(ConfigState $state): void
    {
        $state->setAsJson('assets', $this->assetActionList);
    }
}