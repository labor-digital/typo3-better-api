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
 * Last modified: 2021.04.29 at 22:17
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
 * Last modified: 2020.03.19 at 03:00
 */

namespace LaborDigital\T3ba\Tool\FormEngine\Custom;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3ba\Tool\Rendering\TemplateRenderingService;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderContext;

trait CustomFormElementTrait
{
    use ContainerAwareTrait;
    
    /**
     * @inheritDoc
     */
    public function filterResultArray(array $result): array
    {
        return $result;
    }
    
    /**
     * @inheritDoc
     */
    public static function configureField(AbstractField $field, array $options, TcaBuilderContext $context): void { }
    
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
     * @param   string  $configKey    The key in your field's TCA config that should be searched for
     * @param   array   $arguments    A list of arguments that are passed to the user function
     * @param   null    $defaultData  The default data, which is returned if there was no userFunction registered
     *
     * @return mixed|null
     */
    protected function callUserFunc(
        string $configKey,
        array $arguments = [],
        $defaultData = null
    )
    {
        $config = $this->context->getConfig()['config'] ?? [];
        $callback = $config['t3ba'][$configKey] ?? $config[$configKey] ?? null;
        
        if (empty($callback)) {
            return $defaultData;
        }
        
        return call_user_func_array(NamingUtil::resolveCallable($callback), $arguments);
    }
    
    /**
     * Returns the instance of the template rendering service
     *
     * @return \LaborDigital\T3ba\Tool\Rendering\TemplateRenderingService
     */
    protected function getTemplateRenderer(): TemplateRenderingService
    {
        return $this->getService(TemplateRenderingService::class);
    }
    
    /**
     * This method is a shortcut to $this->getTemplateRenderer()->getFluidView()->render().
     *
     * In addition to being a handy shortcut, this method comes with a lot of variables already pre-configured.
     * When you use this method to render your template you may use in addition to everything you supply using $data.
     * For Wizards and Fields:
     *  - {value} Renders the current field's value
     *  - {renderId} The id of your input field, if you don't want to use inputAttributes
     *  - {renderName} The name of your hidden field to hold the data
     * Only for fields:
     *  - {inputAttributes -> f:format.raw} The html attributes for the real input field (mind the 3 curly braces)
     *  - {hiddenField -> f:format.raw} The preconfigured hidden field to hold your data (mind the 3 curly braces)
     *  - {hiddenAttributes -> f:format.raw} The html attributes for the hidden input field (mind the 3 curly braces)
     *
     * @param   string  $template      Either a full template file name or a file path as EXT:.../template.html, or a
     *                                 file that is relative to the given "templateRootPaths"
     * @param   array   $options       Additional configuration options
     *                                 {@link TemplateRenderingService::getFluidView()} for the options.
     *
     * @return string
     * @see TemplateRenderingService::getFluidView()
     */
    protected function renderFluidTemplate(string $template, array $data = [], array $options = []): string
    {
        $data = $this->enhanceTemplateData($data);
        $view = $this->getTemplateRenderer()->getFluidView($template, $options);
        $view->assignMultiple($data);
        
        return $view->render();
    }
    
    /**
     * This method is a shortcut to $this->getTemplateRenderer()->renderMustache() and as you might have guessed
     * will render a mustache template you will supply.
     *
     * In addition to being a handy shortcut, this method comes with a lot of variables already pre-configured.
     * When you use this method to render your template you may use in addition to everything you supply using $data:
     * For Wizards and Fields:
     *  - {{value}} Renders the current field's value
     *  - {{renderId}} The id of your input field, if you don't want to use inputAttributes
     *  - {{renderName}} The name of your hidden field to hold the data
     * Only for fields:
     *  - {{{inputAttributes}}} The html attributes for the real input field (mind the 3 curly braces)
     *  - {{{hiddenField}}} The preconfigured hidden field to hold your data (mind the 3 curly braces)
     *  - {{{hiddenAttributes}}} The html attributes for the hidden input field (mind the 3 curly braces)
     *
     * @param   string  $template  Either a mustache template as string, or a path like FILE:EXT:...
     * @param   array   $data      The view data to use for the renderer object
     * @param   array   $options   $options LightNCandy compile time- and run time options
     *
     * @return string
     *
     * @see TemplateRenderingService::renderMustache()
     */
    protected function renderTemplate(string $template, array $data = [], array $options = []): string
    {
        $data = $this->enhanceTemplateData($data);
        
        return $this->getTemplateRenderer()->renderMustache($template, $data, $options);
    }
    
    /**
     * Mostly an internal helper to enhance the template data array with additional, useful information.
     *
     * @param   array  $data
     *
     * @return array
     */
    protected function enhanceTemplateData(array $data): array
    {
        if (! isset($data['value'])) {
            $data['value'] = $this->context->getValue();
        }
        
        if (! isset($data['renderId'])) {
            $data['renderId'] = $this->context->getRenderId();
        }
        
        if (! isset($data['renderName'])) {
            $data['renderName'] = $this->context->getRenderName();
        }
        
        return $data;
    }
}
