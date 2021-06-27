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
/*
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
 * Last modified: 2020.08.23 at 23:23
 */

namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\Module;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\ConfigBuilder\FluidTemplateBuilder;
use LaborDigital\T3ba\Tool\Translation\Translator;
use Neunerlei\Arrays\Arrays;
use Neunerlei\FileSystem\Fs;
use Neunerlei\Inflection\Inflector;
use Neunerlei\TinyTimy\DateTimy;

class ConfigGenerator
{
    
    /**
     * @var \LaborDigital\T3ba\Tool\Translation\Translator
     */
    protected $translator;
    
    /**
     * ModuleConfigGenerator constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\Translation\Translator  $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }
    
    /**
     * Generates the configuration array
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Module\ModuleConfigurator  $configurator
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext                          $context
     *
     * @return array
     */
    public function generate(ModuleConfigurator $configurator, ExtConfigContext $context): array
    {
        $this->makeTranslationFileIfRequired($configurator, $context);
        
        $context->getState()->attachToString(
            'typo.typoScript.dynamicTypoScript.extBase\.setup',
            FluidTemplateBuilder::build('module', $configurator->getSignature(), $configurator),
            true
        );
        
        return $this->makeRegisterModuleArgs($configurator, $context);
    }
    
    /**
     * Makes sure the module translation file exists or creates a new one
     *
     * @param   ModuleConfigurator                             $configurator
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext  $context
     */
    protected function makeTranslationFileIfRequired(ModuleConfigurator $configurator, ExtConfigContext $context): void
    {
        // Check if the file exists
        $translationFile = $context->getTypoContext()->path()->typoPathToRealPath($configurator->getTranslationFile());
        if (file_exists($translationFile)) {
            return;
        }
        
        // Check if we got a context
        if ($this->translator->hasNamespace($configurator->getTranslationFile())) {
            $translationFile = $this->translator->getNamespaceFile($configurator->getTranslationFile(), true);
            $configurator->setTranslationFile($translationFile);
            
            return;
        }
        
        // Create new translation file
        $definition = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<xliff version="1.0">
  <file source-language="en" datatype="plaintext" original="messages" date="' .
                      (new DateTimy())->format("Y-m-d\TH:i:s\Z") . '" product-name="' . $context->getExtKey() . '">
    <header/>
    <body>
      <trans-unit id="mlang_tabs_tab">
        <source>' . Inflector::toHuman($context->getExtKey()) . ': '
                      . Inflector::toHuman($configurator->getPluginName()) . '</source>
      </trans-unit>
      <trans-unit id="mlang_labels_tablabel">
        <source>A new and shiny module</source>
      </trans-unit>
      <trans-unit id="mlang_labels_tabdescr">
        <source>A new and shiny module</source>
      </trans-unit>
    </body>
  </file>
</xliff>';
        
        Fs::writeFile($translationFile, $definition);
    }
    
    /**
     * Builds and returns the arguments that have to be passed to the "registerModule" method to add our module to the
     * backend.
     *
     * @param   ModuleConfigurator                             $configurator
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext  $context
     *
     * @return array
     *
     * @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule()
     */
    protected function makeRegisterModuleArgs(ModuleConfigurator $configurator, ExtConfigContext $context): array
    {
        return array_values([
            'extensionName' => $context->getExtKey(),
            'mainModuleName' => $configurator->getSection(),
            'subModuleName' => $configurator->getModuleKey(),
            'position' => $configurator->getPosition(),
            'controllerActions' => $configurator->getActions(),
            'moduleConfiguration' => Arrays::merge(
                $configurator->getAdditionalOptions(),
                [
                    'access' => implode(',', $configurator->getAccess()),
                    'icon' => $configurator->getIcon(),
                    'labels' => $configurator->getTranslationFile(),
                ]
            ),
        ]);
    }
}
