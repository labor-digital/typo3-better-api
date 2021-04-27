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
 * Last modified: 2021.04.22 at 00:24
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Table\ContentType;


use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Core\Di\PublicServiceInterface;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfig\Traits\DelayedConfigExecutionTrait;
use LaborDigital\T3BA\Tool\Tca\ContentType\Builder\Io\Dumper;
use LaborDigital\T3BA\Tool\Tca\ContentType\Builder\Io\Factory;
use LaborDigital\T3BA\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\Arrays\Arrays;

class Loader implements PublicServiceInterface
{
    use ContainerAwareTrait;
    use TypoContextAwareTrait;
    use DelayedConfigExecutionTrait;

    protected const EXT_CONTENT_DEFAULT_TYPE = '__extContentDefaultType__';

    /**
     * @var \LaborDigital\T3BA\Tool\Tca\ContentType\Builder\Io\Factory
     */
    protected $factory;

    /**
     * @var \LaborDigital\T3BA\ExtConfig\ExtConfigContext
     */
    protected $configContext;

    /**
     * @var \LaborDigital\T3BA\Tool\Tca\ContentType\Builder\Io\Dumper
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
    ) {
        $this->factory       = $factory;
        $this->dumper        = $dumper;
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
        // Extract the default type tca
        $defaultTca = Arrays::getPath($GLOBALS, ['TCA', 'tt_content', 'types', static::EXT_CONTENT_DEFAULT_TYPE]);
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

                    // Allow the registering class to provide us with a suggested model class name.
                    // This is useful to inflect the model class based on the controller name
                    if (is_array($additionalData) && is_string($additionalData['modelSuggestion'])
                        && class_exists($additionalData['modelSuggestion'])) {
                        $type->setModelClass($additionalData['modelSuggestion']);
                    }
                }

                call_user_func([$className, 'configureContentType'], $type, $this->configContext);

                $this->dumper->registerType($type);

                $type->ignoreFieldIdIssues(false);
            },
            function () use (&$type) {
                $type = null;
            }
        );

        $GLOBALS['TCA'] = $this->dumper->dump($GLOBALS['TCA'], $this->configContext);

    }

}
