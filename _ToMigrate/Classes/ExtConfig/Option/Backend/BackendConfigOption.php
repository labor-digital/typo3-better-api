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
 * Last modified: 2020.03.18 at 17:34
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\Backend;

use LaborDigital\Typo3BetterApi\Event\Events\BackendAssetFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\CommandRegistrationEvent;
use LaborDigital\Typo3BetterApi\Event\Events\ExtLocalConfLoadedEvent;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\Inflection\Inflector;
use Neunerlei\Options\Options;
use Neunerlei\PathUtil\Path;
use Symfony\Component\Console\Command\Command;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Class BackendConfigOption
 *
 * Can be used to configure the TYPO3 backend
 *
 * @package LaborDigital\Typo3BetterApi\ExtConfig\Option\Backend
 */
class BackendConfigOption extends AbstractExtConfigOption
{
    
    /**
     * The list of backend assets to register
     *
     * @var array
     */
    protected $assets = [];
    
    /**
     * @inheritDoc
     */
    public function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(ExtLocalConfLoadedEvent::class, '__applyExtLocalConf');
        $subscription->subscribe(CommandRegistrationEvent::class, '__applyCommands', ['priority' => 500]);
    }
    
    /**
     * Registers a typo3 command controller class.
     * The class has to extend \TYPO3\CMS\Extbase\Mvc\Controller\CommandController.
     * After registration, if your command looks like: MyExt\SomethingCommandController::myAction()
     *
     * use it like: web/typo3/cli_dispatch.phpsh extbase something:my
     *
     * @param   string  $commandHandler  Either the class name of an extBase or an symfony command handler
     * @param   array   $options         Additional options if a symfony command handler is given
     *                                   - commandName string: By default the command name is generated out of
     *                                   your extension key and the class name (The default for extBase command
     *                                   controllers)
     *                                   but if you set this you can define the command yourself
     *                                   - schedulable bool (TRUE): By default, the command can be used in the scheduler
     *                                   too. You can deactivate this by setting schedulable to false
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Backend\BackendConfigOption
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     */
    public function registerCommand(string $commandHandler, array $options = []): BackendConfigOption
    {
        if ($this->context->TypoContext->getEnvAspect()->isFrontend()) {
            return $this;
        }
        
        // Check if this is a command controller
        if (in_array(CommandController::class, class_parents($commandHandler))) {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][md5($commandHandler)]
                = $this->replaceMarkers($commandHandler);
            
            return $this;
        }
        
        // Check if this is a symfony command
        if (in_array(Command::class, class_parents($commandHandler))) {
            return $this->addToCachedValueConfig('commands', [
                'class'   => $commandHandler,
                'options' => $options,
            ]);
        }
        
        // Invalid argument given
        throw new ExtConfigException('The given command handler: ' . $commandHandler . ' has to extend the ' .
                                     CommandController::class . ' or the ' . Command::class . ' class');
    }
    
    /**
     * Registers a new css file to the backend renderer.
     *
     * @param   string  $cssFile  Use something like EXT:ext_key/Resources/Public/Styles/style.css
     *                            You can use fully qualified url's as well.
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Backend\BackendConfigOption
     */
    public function registerCss(string $cssFile): BackendConfigOption
    {
        $this->assets['css'][md5($cssFile)] = $this->replaceMarkers($cssFile);
        
        return $this;
    }
    
    /**
     * Registers a new js file to the backend renderer.
     *
     * @param   string  $jsFile       Use something like EXT:ext_key/Resources/Public/Scripts/script.js
     *                                You can use fully qualified url's as well.
     * @param   bool    $atTheFooter  By default the script will be added to the page head. If you want to add it to the
     *                                footer of the page, set this to true
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Backend\BackendConfigOption
     */
    public function registerJs(string $jsFile, bool $atTheFooter = false): BackendConfigOption
    {
        $this->assets[$atTheFooter ? 'jsFooter' : 'js'][md5($jsFile)] = $this->replaceMarkers($jsFile);
        
        return $this;
    }
    
    /**
     * Registers a scheduler task definition
     *
     * @param   string  $title    A speaking title for the task
     * @param   string  $class    The handler class for the registered task. Should extend the AbstractTask class
     * @param   string  $desc     An optional description for your task
     * @param   array   $options  Additional configuration options as you would define them in the typo3 array,
     *                            normally.
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Backend\BackendConfigOption
     */
    public function registerSchedulerTask(
        string $title,
        string $class,
        string $desc = '',
        array $options = []
    ): BackendConfigOption {
        $options['extension']                                                   = $this->context->getExtKeyWithVendor();
        $options['title']                                                       = $title;
        $options['description']                                                 = $desc;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][$class] = $options;
        
        return $this;
    }
    
    /**
     * Registers a new module to the typo3 backend
     *
     * @param   string  $configuratorClass
     * @param   string  $pluginName
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Backend\BackendConfigOption
     * @see \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\ExtBaseOption::registerBackendModule()
     */
    public function registerModule(string $configuratorClass, ?string $pluginName = null): BackendConfigOption
    {
        $this->context->OptionList->extBase()->registerBackendModule($configuratorClass, $pluginName);
        
        return $this;
    }
    
    /**
     * Use this method to register your custom RTE configuration for the Typo3 backend.
     *
     * @param   array  $config   The part you would normally write under default.editor.config
     *                           Alternatively: If you provide a "config" key in your array,
     *                           it will automatically be moved to editor.config, all other
     *                           options will be moved to the root preset. This is useful for
     *                           defining "processing" information or similar cases.
     *                           If you don't want a "magic" restructuring of your configuration
     *                           and keep it as you defined it start with an 'editor' => ['config' => []]
     *                           array, which will disable all of our internal restructuring.
     * @param   array  $options  Additional options for the configuration
     *                           - preset string (default): A speaking name/key for the preset you are configuring.
     *                           By default all configuration will be done to the "default" preset
     *                           - useDefaultImports bool (TRUE): By default the Processing.yaml, Base.yaml and
     *                           Plugins.yaml will be auto-imported in your configuration. Set this to false to disable
     *                           this feature
     *                           - imports array: Additional imports that will be added to the generated preset file
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Backend\BackendConfigOption
     * @see https://docs.typo3.org/c/typo3/cms-rte-ckeditor/master/en-us/Configuration/Examples.html
     */
    public function registerRteConfig(array $config, array $options = []): BackendConfigOption
    {
        return $this->addToCachedValueConfig('rteConfig', [
            'config'  => $config,
            'options' => $options,
        ]);
    }
    
    /**
     * @inheritDoc
     */
    public function __applyExtLocalConf()
    {
        // Ignore if not in backend mode
        if (! $this->context->TypoContext->getEnvAspect()->isBackend()) {
            return;
        }
        
        // Register RTE config
        // ===========================================================
        $rteConfigFiles = $this->getCachedValueOrRun('rteConfig', RteConfigGenerator::class);
        foreach ($rteConfigFiles as $key => $file) {
            $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets'][$key] = $file;
        }
        
        // Register the css / js files in the backend
        // ===========================================================
        $this->context->EventBus->addListener(BackendAssetFilterEvent::class,
            function (BackendAssetFilterEvent $event) {
                // Helper to resolve urls
                $urlResolver = function ($url) {
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        return $url;
                    }
                    $path = $this->context->TypoContext->getPathAspect()->typoPathToRealPath($url);
                    $path = Path::makeRelative($path, Path::unifyPath(PATH_site));
                    if (stripos($path, './') === 0) {
                        $path = '.' . $path;
                    }
                    
                    return $path;
                };
                
                // Register files
                if (! empty($this->assets['js'])) {
                    foreach ($this->assets['js'] as $file) {
                        $event->getPageRenderer()
                              ->addJsFile($urlResolver($file), 'text/javascript', false, false, '', true);
                    }
                }
                if (! empty($this->assets['jsFooter'])) {
                    foreach ($this->assets['jsFooter'] as $file) {
                        $event->getPageRenderer()
                              ->addJsFooterFile($urlResolver($file), 'text/javascript', false, false, '', true);
                    }
                }
                if (! empty($this->assets['css'])) {
                    foreach ($this->assets['css'] as $file) {
                        $event->getPageRenderer()->addCssFile($urlResolver($file), 'stylesheet', 'all', '', false);
                    }
                }
            });
    }
    
    /**
     * Event handler to inject the registered CLI commands for the symfony console
     *
     * @param   \LaborDigital\Typo3BetterApi\Event\Events\CommandRegistrationEvent  $event
     */
    public function __applyCommands(CommandRegistrationEvent $event)
    {
        foreach ($this->getCachedValueConfig('commands') as $command) {
            $extKey  = $command['extKey'];
            $class   = $command['value']['class'];
            $options = Options::make($command['value']['options'], [
                'commandName' => [
                    'type'    => 'string',
                    'default' => function () use ($extKey, $class) {
                        return Inflector::toCamelBack($extKey) . ':'
                               . Inflector::toCamelBack(Path::classBasename($class));
                    },
                ],
                'schedulable' => [
                    'type'    => 'bool',
                    'default' => true,
                ],
            ]);
            $event->addCommand($class, $options['commandName'], $options['schedulable']);
        }
    }
}
