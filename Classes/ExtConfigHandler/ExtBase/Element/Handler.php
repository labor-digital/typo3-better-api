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
 * Last modified: 2021.04.20 at 11:02
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\ExtBase\Element;


use LaborDigital\T3BA\ExtConfig\Abstracts\AbstractGroupExtConfigHandler;
use LaborDigital\T3BA\ExtConfig\ExtConfigException;
use LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\SignaturePluginNameMapTrait;
use LaborDigital\T3BA\ExtConfigHandler\ExtBase\ContentElement\ConfigGenerator as CeGenerator;
use LaborDigital\T3BA\ExtConfigHandler\ExtBase\ContentElement\ConfigureContentElementInterface;
use LaborDigital\T3BA\ExtConfigHandler\ExtBase\ContentElement\ContentElementConfigurator;
use LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\ConfigGenerator as PluginGenerator;
use LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\ConfigurePluginInterface;
use LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator;
use Neunerlei\Configuration\Handler\HandlerConfigurator;

class Handler extends AbstractGroupExtConfigHandler
{
    use SignaturePluginNameMapTrait;

    /**
     * @var \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Element\SharedConfig
     */
    protected $config;

    /**
     * @var \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\ConfigGenerator
     */
    protected $pluginGenerator;

    /**
     * @var \LaborDigital\T3BA\ExtConfigHandler\ExtBase\ContentElement\ConfigGenerator
     */
    protected $ceGenerator;

    /**
     * @var CeGenerator|PluginGenerator
     */
    protected $generator;

    /**
     * @var \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator|\LaborDigital\T3BA\ExtConfigHandler\ExtBase\ContentElement\ContentElementConfigurator
     */
    protected $configurator;

    /**
     * A map of class names and their element type "ce" for content element, "plugin" for plugin
     *
     * @var array
     */
    protected $types = [];

    /**
     * Internal flag that defines which configuration method to use on the configuration class
     *
     * @var string
     */
    protected $configMethod;

    /**
     * ExtBasePluginConfigHandler constructor.
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\ConfigGenerator          $pluginGenerator
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\ContentElement\ConfigGenerator  $ceGenerator
     */
    public function __construct(PluginGenerator $pluginGenerator, CeGenerator $ceGenerator)
    {
        $this->config = $this->getInstanceWithoutDi(SharedConfig::class);
        $pluginGenerator->setConfig($this->config);
        $ceGenerator->setConfig($this->config);
        $this->pluginGenerator = $pluginGenerator;
        $this->ceGenerator     = $ceGenerator;
    }

    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $configurator->registerLocation('Classes/Controller');
        $configurator->executeThisHandlerAfter(\LaborDigital\T3BA\ExtConfigHandler\TypoScript\Handler::class);
        $configurator->executeThisHandlerAfter(\LaborDigital\T3BA\ExtConfigHandler\Table\Handler::class);
        $configurator->registerInterface(ConfigurePluginInterface::class);
        $configurator->registerInterface(ConfigureContentElementInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function prepareHandler(): void { }


    /**
     * @inheritDoc
     */
    public function finishHandler(): void
    {
        $this->config->dump($this->context->getState());
    }

    /**
     * @inheritDoc
     */
    protected function getGroupKeyOfClass(string $class): string
    {
        $elementKey = $this->getElementKeyForClass($class, [$this, 'getSignatureFromClass']);

        $interfaces = class_implements($class);

        $classType = null;
        if (in_array(ConfigureContentElementInterface::class, $interfaces, true)) {
            $classType = 'ce';
        }

        if (in_array(ConfigurePluginInterface::class, $interfaces, true)) {
            if ($classType) {
                throw new ExtConfigException(
                    'You can\'t configure a content element and plugin to use the same class, which is what you did for: "'
                    . $class . '"');
            }
            $classType = 'plugin';
        }

        if (isset($this->types[$elementKey]) && $this->types[$elementKey] !== $classType) {
            throw new ExtConfigException(
                'Configuration mismatch, element: ' . $elementKey . ' was registered as ' .
                $this->types[$elementKey] . ' but class: ' . $class . ' uses it as: ' . $classType . '');
        }

        $this->types[$elementKey] = $classType;

        return $elementKey;
    }

    /**
     * @inheritDoc
     */
    public function prepareGroup(string $signature, array $groupClasses): void
    {
        $isContentElement   = $this->types[$signature] === 'ce';
        $confClass          = $isContentElement ? ContentElementConfigurator::class : PluginConfigurator::class;
        $this->configMethod = $isContentElement ? 'configureContentElement' : 'configurePlugin';
        $this->generator    = $isContentElement ? $this->ceGenerator : $this->pluginGenerator;
        $this->configurator = $this->getInstanceWithoutDi($confClass, [
            $signature,
            $this->getPluginNameForSignature($signature),
            $this->context,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function handleGroupItem(string $class): void
    {
        call_user_func([$class, $this->configMethod], $this->configurator, $this->context);
    }

    /**
     * @inheritDoc
     */
    public function finishGroup(string $groupKey, array $groupClasses): void
    {
        $this->generator->generate($this->configurator, $this->context);
    }
}
