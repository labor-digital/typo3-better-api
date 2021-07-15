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


namespace LaborDigital\T3ba\ExtConfigHandler\Frontend;


use InvalidArgumentException;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigConfigurator;
use LaborDigital\T3ba\ExtConfigHandler\Common\Assets\AssetCollectorTrait;
use Neunerlei\Configuration\State\ConfigState;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerInterface;

class FrontendConfigurator extends AbstractExtConfigConfigurator implements NoDiInterface
{
    use AssetCollectorTrait;
    
    /**
     * The list of meta tags that should be added or removed when the page is rendered
     *
     * @var array
     */
    protected $metaTagActions = [];
    
    /**
     * A list of meta tag manager that should be registered
     *
     * @var array
     */
    protected $metaTagManagers = [];
    
    /**
     * Contains the registered html data to append to the page head section
     *
     * @var string|null
     */
    protected $headerHtml;
    
    /**
     * Contains the registered html data to append to the page footer section
     *
     * @var string|null
     */
    protected $footerHtml;
    
    /**
     * Registers a new meta tag manager instance into the registry
     *
     * @param   string          $name       A unique identifier for the manager to register
     * @param   string          $className  The class to register as manager. It must implement MetaTagManagerInterface
     * @param   array|string[]  $before     Optional list of other manager names this manager should be executed before
     * @param   array           $after      Optional list of other manager names this manager should be executed after
     *
     * @return $this
     * @see MetaTagManagerInterface
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/MetaTagApi/Index.html
     */
    public function registerMetaTagManager(
        string $name,
        string $className,
        array $before = ['generic'],
        array $after = []
    ): self
    {
        if (! class_exists($className) || ! in_array(MetaTagManagerInterface::class, class_implements($className), true)) {
            throw new InvalidArgumentException('The given manager "' . $name . '" can\'t be used, because the class: "' .
                                               $className . '" does not implement the required interface: "' . MetaTagManagerInterface::class . '"');
        }
        
        $this->metaTagManagers[$name] = $this->context->replaceMarkers(func_get_args());
        
        return $this;
    }
    
    /**
     * Registers a new meta tag to be added through the meta tag manager
     *
     * @param   string  $property       The name of the property/meta tag that should be added. e.g. og:title
     * @param   string  $content        The content to be given to the meta tag
     * @param   array   $subProperties  If you need to specify sub-properties, e.g. og:image:width,
     *                                  you can use this array like ['width' => 400, 'height' => 400]
     * @param   bool    $replace        If set to true, this meta tag will replace existing ones,
     *                                  otherwise it will simply be ignored
     * @param   string  $type           Defines which attribute is used to store the $property value.
     *                                  By default this is "name"
     *
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/MetaTagApi/Index.html
     */
    public function registerMetaTag(
        string $property,
        string $content,
        array $subProperties = [],
        bool $replace = false,
        string $type = ''
    ): self
    {
        $this->metaTagActions['add'][$property] = $this->context->replaceMarkers(func_get_args());
        
        return $this;
    }
    
    /**
     * Removes a registered meta tag from the list.
     * If there are multiple occurrences of a property, they all will be removed
     *
     * Note: This works even on tags registered through a viewHelper!
     *
     * @param   string  The name of the property/meta tag that should be removed. e.g. og:title
     * @param   string  $type  Defines which attribute is used to store the $property value.
     *                         By default this is "name"
     *
     * @return $this
     */
    public function removeMetaTag(string $property, string $type = ''): self
    {
        $this->metaTagActions['remove'][] = $this->context->replaceMarkers(func_get_args());
        
        return $this;
    }
    
    /**
     * Adds the given RAW html to be added to the header of your page.
     * WARNING: THIS IS RAW HTML - So escape your stuffs!
     *
     * @param   string  $html
     *
     * @return $this
     */
    public function registerHeaderHtml(string $html): self
    {
        $this->headerHtml .= PHP_EOL . trim($this->context->replaceMarkers($html));
        
        return $this;
    }
    
    /**
     * Adds the given RAW html to be added to the footer of your page.
     * WARNING: THIS IS RAW HTML - So escape your stuffs!
     *
     * @param   string  $html
     *
     * @return $this
     */
    public function registerFooterHtml(string $html): self
    {
        $this->footerHtml .= PHP_EOL . trim($this->context->replaceMarkers($html));
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function finish(ConfigState $state): void
    {
        $state->useNamespace('root', function (ConfigState $state) {
            $state->set('t3ba.frontend.metaTagManagers', array_values($this->metaTagManagers));
        });
        $state->set('html', ['header' => $this->headerHtml, 'footer' => $this->footerHtml]);
        $state->set('metaTagActions', $this->metaTagActions);
        $this->storeAssetCollectorConfiguration($state);
    }
}