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
 * Last modified: 2020.03.19 at 03:01
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\FlexForms;

use Exception;
use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractForm;
use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormContainer;
use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement;
use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormField;
use LaborDigital\Typo3BetterApi\BackendForms\BackendFormException;
use LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaField;
use LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable;
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\CoreModding\ClassAdapters\GeneralUtilityAdapter;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use Neunerlei\Arrays\Arrays;
use Neunerlei\FileSystem\Fs;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;

class FlexForm extends AbstractForm
{
    
    /**
     * Our normal period "." does not work very well for flex forms,
     * because there are often keys that have periods in them, themselves.
     * So we use the php object key as a exception to the rule here...
     */
    public const PATH_SEPARATOR = '->';
    
    /**
     * Contains additional metadata that was stored in the head of the flex form config
     *
     * @var array
     */
    protected $meta = [];
    
    /**
     * The form field reference which holds this flex form
     *
     * @var AbstractFormField
     */
    protected $containingField;
    
    /**
     * This method returns the name of the generated flex form file of this definition.
     *
     * To avoid additional overhead we create a copy of EVERY flex form we TOUCH.
     * Even if the definition is only loaded from a file and not changed -> We create a copy.
     * The consequence to avoid that would be A.) to keep track of a "dirty" state or B.) check
     * every file we loaded and see if the content changed... Both is in my opinion not worth it...
     *
     * The method returns null if the file was not yet built.
     *
     * @return string|null
     */
    public function getFileName(): ?string
    {
        if (! $this->context->Fs->hasFile($this->getFlexFormCacheFilePath())) {
            return null;
        }
        
        return $this->context->Fs->getFile($this->getFlexFormCacheFilePath())->getPathname();
    }
    
    /**
     * Returns the cache key under which the flex form definition will be stored
     *
     * @return string
     */
    public function getFlexFormId(): string
    {
        return 'flex-form-' . $this->getId();
    }
    
    /**
     * Internal helper to create the filename of a flex form file for the temp fs lookup
     *
     * @return string
     */
    protected function getFlexFormCacheFilePath(): string
    {
        return 'flexForms/' . $this->getFlexFormId() . '.xml';
    }
    
    /**
     * Note: This method supports the usage of paths. Flexform fields inside of containers may have
     * the same id's. Which makes the lookup of such fields ambiguous. There is not much you can do about that tho...
     * To select such fields you can either select the section and then the field inside of it. Or you use paths
     * on a method like this. A path is sDef->section->field the "->" works as a separator between the parts of the
     * path.
     *
     * @inheritDoc
     */
    public function getElement(string $id)
    {
        // Allow path lookup
        if (stripos($id, static::PATH_SEPARATOR) !== false) {
            try {
                return $this->getElementByPathInternal($id);
            } catch (Exception $e) {
            }
        }
        
        return parent::getElement($id);
    }
    
    
    /**
     * Returns the instance of a certain tab.
     * Note: If the tab not exists, a new one will be created at the end of the form
     *
     * @param   string  $id
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexTab
     */
    public function getTab(string $id): FlexTab
    {
        return $this->getOrCreateElement($id, static::TYPE_TAB, function () use ($id) {
            return $this->context->getInstanceOf(FlexTab::class, [$id, $this->context]);
        });
    }
    
    /**
     * Returns a single section instance
     * Note: If the section not exists, a new one will be created at the end of the form
     *
     * @param   string  $id
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexSection
     */
    public function getSection(string $id): FlexSection
    {
        return $this->getOrCreateElement($id, static::TYPE_CONTAINER, function () use ($id) {
            return $this->context->getInstanceOf(FlexSection::class, [$id, $this->context]);
        });
    }
    
    /**
     * Returns true if the layout has a section with that id already registered
     *
     * @param   string  $id
     *
     * @return bool
     */
    public function hasSection(string $id): bool
    {
        return $this->hasElementInternal($id, static::TYPE_CONTAINER);
    }
    
    /**
     * Returns the list of all sections that are used inside of this form
     *
     * @return array
     */
    public function getSections(): array
    {
        return $this->getAllOfType(static::TYPE_CONTAINER);
    }
    
    /**
     * Returns the instance of a certain field inside your current layout
     *
     * Note: If the field not exists, a new one will be created at the end of the form
     *
     * Note: This method supports the usage of paths. FlexForm fields inside of containers may have
     * the same id's. Which makes the lookup of such fields ambiguous. There is not much you can do about that tho...
     * To select such fields you can either select the section and then the field inside of it. Or you use paths
     * on a method like this. A path is sDef->section->field the "->" works as a separator between the parts of the
     * path.
     *
     * @param   string  $id
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexField
     */
    public function getField(string $id): FlexField
    {
        // Field instance generator
        $generator = function ($_id = null) use ($id) {
            if (! empty($_id)) {
                $id = $_id;
            } // Override external id when path lookup is used...
            $tca = isset($this->config[$id]) ? $this->config[$id] : static::DEFAULT_FIELD_CONFIG;
            unset($this->config[$id]);
            
            return FlexField::makeFromTcaConfig($id, $tca, $this->context, $this);
        };
        
        // Allow path lookup
        if (stripos($id, static::PATH_SEPARATOR) !== false) {
            try {
                return $this->getOrCreateElementByPath($id, $generator);
            } catch (Exception $e) {
            }
        }
        
        // Default lookup
        return $this->getOrCreateElement($id, static::TYPE_ELEMENT, $generator);
    }
    
    /**
     * You can use this method to load a new flex form definition into your current form.
     * Note: This will overwrite your current configuration!.
     *
     * A definition may either be a file path like: FILE:EXT:$your_ext/.../flexForm.xml
     * A definition may also be a valid flex form string
     * In addition a definition may also be only the base name of the flex form file, like "flexForm"
     * this will then automatically look for the flex form definition in your current extension's Configuration
     * directory.
     *
     * @param   string  $definition
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexForm
     * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
     */
    public function loadDefinition(string $definition): FlexForm
    {
        // Convert the definition into an array to make it parsable
        $definition = trim($definition);
        if (substr(strtolower($definition), 0, 5) === 'file:') {
            // Load a static file
            $filePath = $this->context->TypoContext->getPathAspect()->typoPathToRealPath($definition);
            if (! file_exists($filePath)) {
                // Check if this was a shortname for a flexform
                $filePath = basename(substr($definition, 5));
                $filePath = 'FILE:EXT:' . $this->context->getExtKey() . '/Configuration/FlexForms/' . $filePath;
                if (substr($filePath, -4) !== '.xml') {
                    $filePath .= '.xml';
                }
                
                // Try again
                $filePath = $this->context->TypoContext->getPathAspect()->typoPathToRealPath($filePath);
                if (! file_exists($filePath)) {
                    throw new BackendFormException('Could not load the flexform file at: ' . $definition
                                                   . '! The file does not exist', 999);
                }
            }
            // Load the definition
            $definition = Fs::readFile($filePath);
        }
        
        // Convert the definition into an array
        $definitionArray = GeneralUtilityAdapter::xml2arrayWithoutCache($definition);
        
        // Check if we have sheets or forcefully create them
        if (! isset($definitionArray['sheets'])) {
            if (isset($definitionArray['sDEF'])) {
                $sheets = $definitionArray;
            } elseif (isset($definitionArray['ROOT'])) {
                $sheets = ['sDEF' => $definitionArray];
            } else {
                throw new BackendFormException('Could not load flex form, as it does not define any "sheets".');
            }
            $definitionArray['sheets'] = $sheets;
        }
        
        $walker = function (array $list, array $path, $walker) {
            // Check what we should handle now...
            if (empty($path)) {
                // Currently iterating sheets
                foreach ($list as $k => $sheet) {
                    // Add new tabs
                    $tab = $this->getTab($k);
                    
                    // Try to load the label
                    $label = Arrays::getPath($sheet, ['ROOT', 'TCEforms', 'sheetTitle']);
                    if (! empty($label)) {
                        $tab->setLabel($label);
                    }
                    
                    // Try to iterate my children
                    $children = Arrays::getPath($sheet, ['ROOT', 'el'], []);
                    if (! empty($children)) {
                        $walker($children, [$k], $walker);
                    }
                }
                
                return;
            } elseif (count($path) === 1) {
                // Iterating sheet objects
                foreach ($list as $k => $element) {
                    // Test for containers
                    if (! empty($element['section'])) {
                        $children = Arrays::getPath($element, ['el'], []);
                        if (empty($children)) {
                            continue;
                        }
                        
                        // Get the first element -> This is the element which is then repeatable
                        $container = reset($children);
                        $section   = $this->getSection($k);
                        $section->setContainerItemId((string)key($children));
                        
                        // Load section labels
                        if (! empty($element['title'])) {
                            $section->setLabel($element['title']);
                        }
                        if (! empty($container['title'])) {
                            $section->setContainerItemLabel($container['title']);
                        }
                        
                        // Check for children in the container
                        $children = Arrays::getPath($container, ['el'], []);
                        if (empty($children)) {
                            continue;
                        }
                        
                        // Loop over the items inside this container
                        foreach ($children as $_k => $_element) {
                            if (! empty($_element['TCEforms'])) {
                                $this->config[$_k] = $_element['TCEforms'];
                                $this->getField(implode(static::PATH_SEPARATOR, Arrays::attach($path, [$k, $_k])));
                            } elseif (is_array($_element['config'])
                                      && (isset($_element['config']['type'])
                                          || isset($_element['config']['renderType']))) {
                                // Check if the element is wrongly formatted
                                $this->config[$_k] = $_element;
                                $this->getField(implode(static::PATH_SEPARATOR, Arrays::attach($path, [$k, $_k])));
                            }
                        }
                    }
                    
                    // Test for element
                    if (! empty($element['TCEforms'])) {
                        $this->config[$k] = $element['TCEforms'];
                        $this->getField($k);
                    } elseif (is_array($element['config'])
                              && (isset($element['config']['type'])
                                  || isset($element['config']['renderType']))) {
                        // Check if the element is wrongly formatted
                        $this->config[$k] = $element;
                        $this->getField($k);
                    }
                }
            }
        };
        
        // Get meta if there is some
        if (! empty($definitionArray['meta'])) {
            $this->meta = $definitionArray['meta'];
        }
        
        // Check if there are sheets -> Start the recursive walker
        if (! empty($definitionArray['sheets'])) {
            $walker($definitionArray['sheets'], [], $walker);
        }
        
        return $this;
    }
    
    /**
     * Returns the generated xml configuration for the current flex form instance
     *
     * @return string
     */
    public function getDefinition(): string
    {
        return $this->dumpXmlDefinition();
    }
    
    /**
     * Returns the form field which holds this flex form
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormField
     */
    public function getContainingField(): AbstractFormField
    {
        return $this->containingField;
    }
    
    /**
     * Internal helper to dump the definition into the cache file
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexForm
     */
    public function __build(): FlexForm
    {
        // Dump the definition and store it into a cached value
        $definition = $this->dumpXmlDefinition();
        $this->context->Fs->setFileContent($this->getFlexFormCacheFilePath(), $definition);
        
        return $this;
    }
    
    /**
     * Factory method to create new form instances
     *
     * @param   string                                                       $definition
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext      $context
     * @param   \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaField  $field
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexForm
     */
    public static function makeInstance(string $definition, ExtConfigContext $context, TcaField $field): FlexForm
    {
        $id = Inflector::toUuid($field->getForm()->getTableName() . $field->getColumnName() . $field->getForm()->getId()
                                . $field->getForm()->getTypeKey());
        $i  = TypoContainer::getInstance()->get(static::class, ['args' => [$id, $context]]);
        $i->loadDefinition($definition);
        $i->ensureInitialTab(true);
        $i->containingField = $field;
        
        // Make sure we can retrieve the filename
        $context->Fs->setFileContent($i->getFlexFormCacheFilePath(), '');
        
        return $i;
    }
    
    /**
     * Can be used to create a new flex form instance that is not linked to any database table.
     * Can be used if another ext config element requires a flex form definition.
     *
     * @param   ExtConfigContext  $context     The ext config context object
     * @param   string|null       $definition  Optional, initial flex form definition
     * @param   string|null       $tableName   Optional, name of the table to generate the flex form for
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexForm
     * @todo Merge this with makeInstance() when putting them into the constructor
     */
    public static function makeStandaloneInstance(
        ExtConfigContext $context,
        ?string $definition = null,
        ?string $tableName = null
    ): FlexForm {
        if (is_null($tableName)) {
            $tableName = 'pseudoFlexFormTable-' . md5(microtime(true));
        }
        $table = TcaTable::makeInstance($tableName, $context);
        $table->getType('default');
        $flexForm = $table->getField('flexFormField-' . md5(microtime(true)))->getFlexFormConfig()->getForm();
        if (! empty($definition)) {
            $flexForm->loadDefinition($definition);
        } else {
            $flexForm->loadDefinition('<T3DataStructure><sheets type=\'array\'></sheets></T3DataStructure>');
        }
        
        return $flexForm;
    }
    
    /**
     * @inheritDoc
     */
    protected function ensureInitialTab(bool $internal = false)
    {
        if (! $internal) {
            return;
        }
        if (! empty($this->elements)) {
            return;
        }
        $this->getTab('sDEF')->setLabel('LLL:EXT:lang/locallang_core.xlf:labels.generalTab');
    }
    
    /**
     * @inheritDoc
     */
    protected function getElementInternal(string $id, int $type = self::TYPE_ALL, array &$finds = null)
    {
        // Allow path lookup
        if (stripos($id, static::PATH_SEPARATOR) !== false) {
            // Try to resolve the element using the path
            try {
                $result = $this->getElementByPathInternal($id);
                switch ($type) {
                    case self::TYPE_ALL:
                        return $result;
                    case self::TYPE_ELEMENT:
                        if (! $result instanceof AbstractFormField) {
                            throw new Exception();
                        }
                        
                        return $result;
                    case self::TYPE_CONTAINER:
                        if (! $this->elIsContainer($result)) {
                            throw new Exception();
                        }
                        
                        return $result;
                    case static::TYPE_TAB:
                        if (! $this->elIsTab($result)) {
                            throw new Exception();
                        }
                        
                        return $result;
                }
            } catch (Exception $e) {
            }
        }
        
        return parent::getElementInternal($id, $type, $finds);
    }
    
    /**
     * Internal helper which takes a path and unifies it into an array
     *
     * @param   array|string  $path
     *
     * @return array
     * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
     */
    protected function parsePath($path): array
    {
        // Prepare the path
        if (is_string($path)) {
            $path = Arrays::parsePath($path, static::PATH_SEPARATOR);
        }
        if (! is_array($path) || empty($path)) {
            throw new BackendFormException('Invalid path given! ' . json_encode($path));
        }
        
        return $path;
    }
    
    /**
     * Basically the same as getOrCreateElement() but works with a path instead of an id.
     *
     * The generator will receive the real id of the element as a parameter.
     *
     * @param   array|string  $path       The path to the element to retrieve
     * @param   callable      $generator  The method to create a new instance of the element
     *
     * @return FlexForm|AbstractFormElement|mixed
     * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
     */
    protected function getOrCreateElementByPath($path, callable $generator)
    {
        $path = $this->parsePath($path);
        try {
            return $this->getElementByPathInternal($path);
        } catch (BackendFormException $exception) {
            // Prepare id and parent path
            $parentPath = $path;
            $id         = array_pop($parentPath);
            
            // Find the parent or die...
            $parent = $this->getElementByPathInternal($parentPath);
            
            // Use the generator to create a new element
            $i = call_user_func($generator, $id);
            if (! $i instanceof AbstractFormElement) {
                throw new BackendFormException('Error while generating an element! The result of the generator should be a child of AbstractFormElement!');
            }
            
            // Register the new element
            return $parent->addElement($i, '');
        }
    }
    
    /**
     * Internal helper to resolve a path into a form element instance
     *
     * @param $path
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexForm
     * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
     */
    protected function getElementByPathInternal($path)
    {
        $path = $this->parsePath($path);
        
        // Iterate through the path
        $pointer = $this;
        foreach ($path as $part) {
            if (! $pointer instanceof AbstractFormContainer) {
                continue;
            }
            $children = $pointer->getChildren();
            if (isset($children[$part])) {
                $pointer = $children[$part];
            } elseif (isset($children['_' . $part])) {
                $pointer = $children['_' . $part];
            } else {
                throw new BackendFormException('Could not find the element with path: ' . implode(' -> ', $path));
            }
        }
        
        return $pointer;
    }
    
    /**
     * Internal helper to convert the object definition into a xml version of this form
     *
     * @return string
     */
    protected function dumpXmlDefinition(): string
    {
        $out = [];
        
        // Add meta information
        if (! empty($this->meta)) {
            $out['meta'] = $this->meta;
        }
        
        // Build sheet structure
        $sheets = [];
        foreach ($this->getLayoutArray() as $tabId => $tabChildren) {
            /** @var \LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexTab $tab */
            $tab = $tabChildren['@node'];
            $t   = [];
            
            // Add label if there is one
            if (! empty($tab->getLabel())) {
                $t['TCEforms']['sheetTitle'] = $tab->getLabel();
            }
            $t['type'] = 'array';
            $t['el']   = [];
            
            // Run through the children
            unset($tabChildren['@node']);
            foreach ($tabChildren as $k => $child) {
                /** @var AbstractFormElement $child */
                // Check if we got a section
                if (! is_numeric($k) && is_array($child)) {
                    // Handle section
                    /** @var \LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexSection $section */
                    $section = $child['@node'];
                    unset($child['@node']);
                    
                    // Run through all the children
                    $sEl = [];
                    foreach ($child as $_child) {
                        /** @var AbstractFormElement $_child */
                        $sEl[$_child->getId()] = ['TCEforms' => $_child->getRaw()];
                    }
                    
                    // Build section definition
                    $s          = [];
                    $s['title'] = $section->getContainerItemLabel();
                    $s['type']  = 'array';
                    $s['el']    = $sEl;
                    
                    // Wrap the definition in the outer container
                    $sOuter = $section->config;
                    if (! empty($section->getLabel())) {
                        $sOuter['title'] = $section->getLabel();
                    }
                    $sOuter['section']                            = 1;
                    $sOuter['type']                               = 'array';
                    $sOuter['el'][$section->getContainerItemId()] = $s;
                    
                    // Add to tab
                    $t['el'][$section->getId()] = $sOuter;
                } else {
                    // Handle field
                    $t['el'][$child->getId()] = ['TCEforms' => $child->getRaw()];
                }
            }
            $sheets[$tabId] = ['ROOT' => $t];
        }
        
        // Store the sheet list
        $out['sheets'] = $sheets;
        
        // Build the flex form definition
        return $this->context->getInstanceOf(FlexFormTools::class)->flexArray2Xml($out);
    }
}
