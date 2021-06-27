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


namespace LaborDigital\T3ba\ExtConfigHandler\Table\ContentType;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfig\Traits\DelayedConfigExecutionTrait;
use LaborDigital\T3ba\Tool\Tca\ContentType\Builder\ContentType;
use LaborDigital\T3ba\Tool\Tca\ContentType\Builder\Io\Dumper;
use LaborDigital\T3ba\Tool\Tca\ContentType\Builder\Io\Factory;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\Inflection\Inflector;
use ReflectionClass;

class Loader implements PublicServiceInterface
{
    use ContainerAwareTrait;
    use TypoContextAwareTrait;
    use DelayedConfigExecutionTrait;
    
    protected const EXT_CONTENT_DEFAULT_TYPE = '__extContentDefaultType__';
    
    /**
     * Key in "additionalData" that will be used as suggested data model if
     * it contains a class and said class exists
     */
    public const MODEL_SUGGESTION_OPTION = '@modelSuggestion';
    
    /**
     * Key in "additionalData" that will be used as element variant key if a string is provided
     */
    public const VARIANT_NAME_OPTION = '@variantName';
    
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\ContentType\Builder\Io\Factory
     */
    protected $factory;
    
    /**
     * @var \LaborDigital\T3ba\ExtConfig\ExtConfigContext
     */
    protected $configContext;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\ContentType\Builder\Io\Dumper
     */
    protected $dumper;
    
    /**
     * The state of the loaded tca types to prevent double loading in the install tool
     *
     * @var array
     */
    protected $loaded;
    
    public function __construct(
        Factory $factory,
        Dumper $dumper,
        ExtConfigContext $configContext
    )
    {
        $this->factory = $factory;
        $this->dumper = $dumper;
        $this->configContext = $configContext;
    }
    
    /**
     * Provides a dummy default type to the tt_content table, to ensure that the ext content forms
     * can benefit when a TCA override registers new elements on all tt_content types.
     */
    public function provideDefaultTcaType(): void
    {
        $GLOBALS['TCA']['tt_content']['types'][static::EXT_CONTENT_DEFAULT_TYPE]
            = $this->factory->getDefaultTypeTca();
    }
    
    /**
     * Iterates all registered content type configuration classes and builds the TCA extension for them.
     */
    public function load(): void
    {
        if (isset($this->loaded)) {
            foreach ($this->loaded as $tableName => $config) {
                $GLOBALS['TCA'][$tableName] = $config;
            }
            
            return;
        }
        
        // Extract the default type tca
        $defaultTca = $GLOBALS['TCA']['tt_content']['types'][static::EXT_CONTENT_DEFAULT_TYPE] ?? [];
        unset($GLOBALS['TCA']['tt_content']['types'][static::EXT_CONTENT_DEFAULT_TYPE]);
        if (! empty($defaultTca) && is_array($defaultTca)) {
            $this->factory->setDefaultTypeTca($defaultTca);
        }
        
        // Fix for the install tool where the tca gets loaded twice
        if (isset($this->loaded)) {
            foreach ($this->loaded as $signature => $config) {
                $GLOBALS['TCA']['tt_content']['types'][$signature] = $config;
            }
            
            return;
        }
        
        $type = null;
        $this->runDelayedConfig(
            $this->getTypoContext()->config()->getConfigState(),
            $this->configContext,
            'tca.contentTypes',
            function (string $className, string $signature, $_, $additionalData) use (&$type) {
                if ($type === null) {
                    $type = $this->factory->getType($signature);
                    
                    if (is_array($additionalData)) {
                        // Allow the registering class to provide us with a suggested model class name.
                        // This is useful to inflect the model class based on the controller name
                        if (is_string($additionalData[static::MODEL_SUGGESTION_OPTION])
                            && class_exists($additionalData[static::MODEL_SUGGESTION_OPTION])) {
                            $type->setDataModelClass($additionalData[static::MODEL_SUGGESTION_OPTION]);
                        }
                        
                        // Inject the provided variant name if possible
                        if (! empty($additionalData[static::VARIANT_NAME_OPTION]) &&
                            is_string($additionalData[static::VARIANT_NAME_OPTION])) {
                            ContentType::bindVariant($type, $additionalData[static::VARIANT_NAME_OPTION]);
                        }
                    }
                }
                
                // Check if variant method exists
                $configMethod = 'configureContentType';
                if ($type->getVariant() !== null) {
                    $variantConfigMethod = 'configure' . Inflector::toCamelCase($type->getVariant()) . 'ContentType';
                    if (method_exists($className, $variantConfigMethod) &&
                        (new ReflectionClass($className))->getMethod($variantConfigMethod)->isStatic()) {
                        $configMethod = $variantConfigMethod;
                    }
                }
                
                $className::$configMethod($type, $this->configContext);
                
                $this->dumper->registerType($type);
                
                $type->ignoreFieldIdIssues(false);
            },
            function () use (&$type) {
                $type = null;
            }
        );
        
        $this->loaded = $this->dumper->dump($GLOBALS['TCA'], $this->configContext);
        foreach ($this->loaded as $tableName => $config) {
            $GLOBALS['TCA'][$tableName] = $config;
        }
        
    }
    
}
