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
 * Last modified: 2020.03.18 at 13:47
 */

namespace LaborDigital\Typo3BetterApi\TypoScript;

use LaborDigital\Typo3BetterApi\BetterApiException;
use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use LaborDigital\Typo3BetterApi\Event\Events\ExtTablesLoadedEvent;
use LaborDigital\Typo3BetterApi\Event\Events\TcaCompletelyLoadedEvent;
use LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs;
use LaborDigital\Typo3BetterApi\NamingConvention\Naming;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use Neunerlei\Inflection\Inflector;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class TypoScriptService
 * @package LaborDigital\Typo3BetterApi\TypoScript
 *
 * @property ConfigurationManagerInterface $ConfigManager
 * @property TypoScriptParser              $TsParser
 * @property TempFs                        $Fs
 */
class TypoScriptService implements SingletonInterface, LazyEventSubscriberInterface
{
    use CommonServiceLocatorTrait;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigurationManager
     */
    protected $cm;
    
    /**
     * True as soon as the event was fired to block all further setter calls.
     * @var bool
     */
    protected $tsEventFired = false;
    
    /**
     * True as soon as the event was fired to block all further setter calls.
     * @var bool
     */
    protected $tsConfigEventFired = false;
    
    /**
     * The list of all registered typoScript we have to apply
     * @var array
     */
    protected $registeredScripts = [];
    
    /**
     * TypoScript constructor.
     *
     * @param \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigurationManager $cm
     */
    public function __construct(TypoScriptConfigurationManager $cm)
    {
        $this->cm = $cm;
        $this->addToServiceMap([
            'ConfigManager' => ConfigurationManagerInterface::class,
            'TsParser'      => TypoScriptParser::class,
            'Fs'            => TempFs::makeInstance('typoScript'),
        ]);
    }
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(ExtTablesLoadedEvent::class, '__injectPageTs', ['priority' => -200]);
        $subscription->subscribe(TcaCompletelyLoadedEvent::class, '__injectTypoScript', ['priority' => 200]);
    }
    
    /**
     * This method can be used to retrieve typoScript constants from the template.
     *
     * @param null|string|array $path    Either a key or a path like "config.lang" to query the hierarchy. If left
     *                                   empty, the method will return the complete typoScript array.
     *
     * @param array             $options Additional options
     *                                   - default (mixed): By default the method returns null, if the queried value
     *                                   was not found in the configuration. If this option is set, the given value
     *                                   will be returned instead.
     *                                   - pid (integer): An optional pid to query the typoScript for.
     *                                   - separator (string) ".": A separator trough which the path parts are
     *                                   separated from each other
     *
     * @return mixed
     */
    public function getConstants($path = null, array $options = [])
    {
        $options = Options::make($options, [
            'default'   => null,
            'pid'       => null,
            'separator' => '.',
        ]);
        
        // Load configuration
        if (!empty($options['pid'])) {
            $this->cm->setCurrentPid($options['pid']);
        }
        $constants = $this->cm->getTypoScriptConstants();
        if (!empty($options['pid'])) {
            $this->cm->resetCurrentPid();
        }
        
        // Read contents
        return $this->getPathHelper($constants, $path, $options);
    }
    
    /**
     * This method can be used to retrieve typoScript setup from the template.
     *
     * @param null|string|array $path    Either a key or a path like "config.lang" to query the hierarchy. If left
     *                                   empty, the method will return the complete typoScript array.
     * @param array             $options Additional options
     *                                   - default (mixed): By default the method returns null, if the queried value
     *                                   was not found in the configuration. If this option is set, the given value
     *                                   will be returned instead.
     *                                   - pid (integer): An optional pid to query the typoScript for.
     *                                   - separator (string) ".": A separator trough which the path parts are
     *                                   separated from each other
     *                                   - getType (bool) FALSE: If set to TRUE the method will try return
     *                                   the typoScript object's type instead of it's value.
     *                                   The Type is normally stored as: key.key.type
     *                                   while the value is stored as: key.key.type. <- Note the period
     *                                   Not all elements have a type. If we don't fine one we will return the
     *                                   "default" value Otherwise we will try to get the value, and if not set return
     *                                   the type
     *
     * @return array|mixed|null
     */
    public function get($path = null, array $options = [])
    {
        $options = Options::make($options, [
            'default'   => null,
            'pid'       => null,
            'separator' => '.',
            'getType'   => false,
        ]);
        
        // Load configuration
        if (!empty($options['pid'])) {
            $this->cm->setCurrentPid($options['pid']);
        } else {
            $this->cm->setCurrentPid($this->TypoContext->getPidAspect()->getCurrentPid());
        }
        $config = $this->cm->getTypoScriptSetup();
        $this->cm->resetCurrentPid();
        
        // Read contents
        return $this->getPathHelper($config, $path, $options);
    }
    
    /**
     * Returns the plugin / extension configuration for ext base extensions
     *
     * @param string|null $extensionName The extension name / key to read the configuration for
     * @param string|null $pluginName    Optional plugin to look up.
     *
     * @return array
     */
    public function getExtBaseSettings(?string $extensionName = null, ?string $pluginName = null)
    {
        $settings = $this->ConfigManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, $extensionName, $pluginName);
        return !empty($settings) && is_array($settings) ? $settings : $this->cm->getConfiguration($extensionName, $pluginName);
    }
    
    /**
     * This is an alternative to ExtensionManagementUtility's addTypoScript(), but it will not
     * just add the typoScript to the default rendering. It will simulate static files, which you can (and have to)
     * add to your template in the backend's typoScript UI.
     *
     * If you don't specify a "title" all your input is combined into a single, dynamic template which is called
     * "BetterApi - Dynamic TypoScript (typo3_better_api)"
     *
     * If you want your configuration to be separated from this global file, you can always specify a different
     * title in the options which results in a new file being created for you.
     *
     * @param string $setup   The typoScript you want to add to your template
     * @param array  $options Additional options
     *                        - extension (string): Descriptive only. Shows beside the title in the backend UI
     *                        - title (string): A unique title for your template. NOTE: If multiple addToSetup() or
     *                        addConstant() calls refer to the same title, their content will be merged into a single
     *                        file
     *                        - constants (string): Can be used to pass along additional constants for your script
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptService
     */
    public function addSetup(string $setup, array $options = []): TypoScriptService
    {
        $this->validateEventIsNotFired();
        $options = Options::make($options, [
            'extension' => [
                'type'    => ['string'],
                'filter'  => function ($v) {
                    return Naming::extkeyWithoutVendor($v);
                },
                'default' => 'typo3_better_api',
            ],
            'title'     => [
                'type'    => ['string'],
                'default' => 'BetterApi - Dynamic TypoScript',
            ],
            'constants' => [
                'type'    => ['string'],
                'default' => '',
            ],
        ]);
        
        // Create or load set
        $id = $options['extension'] . $options['title'];
        if (!isset($this->registeredScripts['ts'][$id])) {
            $this->registeredScripts['ts'][$id] = [
                'constants' => [],
                'setup'     => [],
                'title'     => $options['title'],
                'extension' => $options['extension'],
            ];
        }
        
        // Add the source to the set
        $this->registeredScripts['ts'][$id]['setup'][] = trim($setup);
        $this->registeredScripts['ts'][$id]['constants'][] = trim($options['constants']);
        return $this;
    }
    
    /**
     * Wrapper for addSetup() which adds the include for a given path to the typoScript setup
     *
     * @param string $path
     * @param array  $options Additional options
     *                        - extension (string): Descriptive only. Shows beside the title in the backend UI
     *                        - title (string): A unique title for your template. NOTE: If multiple addToSetup() or
     *                        addConstant() calls refer to the same title, their content will be merged into a single
     *                        file
     *                        - constants (string): Can be used to pass along additional constants for your script
     *
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptService
     */
    public function addFileToSetup(string $path, array $options = []): TypoScriptService
    {
        return $this->addSetup('<INCLUDE_TYPOSCRIPT: source="FILE:' . $path . '">', $options);
    }
    
    /**
     * This is quite similar to addSetup() but only for typoScript constants
     *
     * @param string $constants The typoScript you want to add to your template
     * @param array  $options   Additional options
     *                          - extension (string): Descriptive only. Shows beside the title in the backend UI
     *                          - title (string): A unique title for your template. NOTE: If multiple addToSetup() or
     *                          addConstant() calls refer to the same title, their content will be merged into a single
     *                          file
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptService
     */
    public function addConstants(string $constants, array $options = []): TypoScriptService
    {
        $this->validateEventIsNotFired();
        $options['constants'] = $constants;
        return $this->addSetup('', $options);
    }
    
    /**
     * Adds a static setup.txt and constants.txt from your extensions Configuration/TypoScript directory
     * to the backend list. Use this either in ext_tables or ext_localconf it works in both.
     *
     * @param string $extension The $_EXTKEY of the extension to register the the file for
     * @param string $path      default('Configuration/TypoScript/') The default typoScript configuration. But can be
     *                          any path inside the given extension
     * @param string $title     An optional title to be displayed in the backend
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptService
     */
    public function addStaticTsDirectory(string $extension, string $path = 'Configuration/TypoScript/', string $title = ''): TypoScriptService
    {
        $this->validateEventIsNotFired();
        
        // Fix incorrect path's that start with EXT:something...
        if (stripos($path, 'ext:') === 0) {
            $path = preg_replace('~(ext):/?.*?/~si', '', $path);
        }
        $this->registeredScripts['tsFiles'][] = [$extension, $path, $title];
        return $this;
    }
    
    /**
     * Shortcut, reminder and bridge to ExtensionManagementUtility::addPageTSConfig.
     * Let's you add pageTsConfig to the configuration tree
     *
     * @param string $config The page ts config to append
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptService
     */
    public function addPageTsConfig(string $config): TypoScriptService
    {
        $this->validateEventIsNotFired(true);
        $config = '# INJECTED WITH TYPOSCRIPT SERVICE - addPageTsConfig' . PHP_EOL . $config;
        $this->registeredScripts['pageTs'][] = $config;
        return $this;
    }
    
    /**
     * Registers a file which can be selected in the "TyposScript Configuration" section of page records in the page
     * backend. A registered file is not globally included but only on the pages it was selected.
     *
     * @param string $extension              The $_EXTKEY of the extension to register the the file for
     * @param string $path                   The path of the file to include.
     *                                       The path should start with EXT:$_EXTKEY/...
     * @param string $title                  Can be used to define a
     *                                       visible label for the file in the backend. If omitted one is
     *                                       auto-generated
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptService
     */
    public function addSelectablePageTsConfigFile(string $extension, string $path, ?string $title = null): TypoScriptService
    {
        $this->validateEventIsNotFired(true);
        if (empty($title) || !is_string($title)) {
            $title = 'Page Ts Config: ' .
            Inflector::toHuman($extension) . ' - ' . Inflector::toHuman(basename($path));
        }
        $this->registeredScripts['selectablePageTs'][] = [
            'extKey' => $extension,
            'path'   => $path,
            'title'  => $title,
        ];
        return $this;
    }
    
    /**
     * Shortcut, reminder and bridge to ExtensionManagementUtility::addUserTSConfig.
     * Let's you add userTsConfig to the configuration tree
     *
     * @param string $config The page ts config to append
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptService
     */
    public function addUserTsConfig(string $config): TypoScriptService
    {
        $this->validateEventIsNotFired(true);
        $config = '# INJECTED WITH TYPOSCRIPT SERVICE - addUserTsConfig' . PHP_EOL . $config;
        $this->registeredScripts['userTs'][] = $config;
        return $this;
    }
    
    /**
     * Parses the given typoScript configuration into an array and returns the result
     *
     * @param string $config
     *
     * @return array
     */
    public function parse(string $config): array
    {
        $parser = $this->TsParser;
        $parser->parse($config);
        return $parser->setup;
    }
    
    /**
     * Removes the tailing dot's from the given definition of parsed typoScript.
     *
     * @param array $config    The typoScript config to remove the dot's from
     * @param bool  $keepTypes By default the object types are moved into a \@type property of the child. If you don't
     *                         want that set this to false. NOTE: In that case you will loose the types.
     *
     * @return array
     */
    public function removeDots(array $config, bool $keepTypes = true)
    {
        $out = [];
        foreach ($config as $k => $v) {
            $keyWithoutDot = rtrim($k, '.');
            if (is_array($v)) {
                if ($keepTypes && $k !== $keyWithoutDot && isset($config[$keyWithoutDot])) {
                    $v['@type'] = $config[$keyWithoutDot];
                }
                $out[$keyWithoutDot] = $this->removeDots($v, $keepTypes);
                continue;
            }
            $out[$keyWithoutDot] = $v;
        }
        return $out;
    }
    
    /**
     * Renders a content object with a given type, based on the given configuration
     *
     * @param string $type   The content object name, eg. "TEXT" or "USER" or "IMAGE"
     * @param array  $config The array with TypoScript properties for the content object
     *
     * @return string
     */
    public function renderContentObject(string $type, array $config)
    {
        return $this->Simulator->runWithEnvironment(['ignoreIfFrontendExists'], function () use ($type, $config) {
            return $this->Tsfe->getContentObjectRenderer()->cObjGetSingle($type, $config);
        });
    }
    
    /**
     * Renders an existing content element, based on the configuration set via typoScript.
     *
     * @param string|array $selector The access path where to find the content element in typoScript
     *
     * @return string
     * @throws \LaborDigital\Typo3BetterApi\BetterApiException
     */
    public function renderContentObjectWith($selector)
    {
        $type = $this->get($selector, ['getType']);
        $config = $this->get($selector);
        if (empty($type) || empty($config)) {
            throw new BetterApiException("The given selector $selector is not a valid cObject");
        }
        return $this->renderContentObject($type, $config);
    }
    
    /**
     * Event handler to inject the ts config into typo3
     */
    public function __injectPageTs()
    {
        $this->tsConfigEventFired = true;
        
        // Register page ts
        if (!empty($this->registeredScripts['pageTs'])) {
            foreach ($this->registeredScripts['pageTs'] as $script) {
                ExtensionManagementUtility::addPageTSConfig($script);
            }
        }
        unset($this->registeredScripts['pageTs']);
        
        // Register selectable page ts
        if (!empty($this->registeredScripts['selectablePageTs'])) {
            foreach ($this->registeredScripts['selectablePageTs'] as $config) {
                // Make sure that the ext key is removed from the beginning
                if (stripos($config['path'], 'ext:' . $config['extKey']) !== false) {
                    $config['path'] = substr($config['path'], strlen($config['extKey']) + 4);
                }
                $config['path'] = trim($config['path'], '\\/');
                ExtensionManagementUtility::registerPageTSConfigFile($config['extKey'], $config['path'], $config['title']);
            }
        }
        unset($this->registeredScripts['selectablePageTs']);
        
        // Register user ts
        if (!empty($this->registeredScripts['userTs'])) {
            foreach ($this->registeredScripts['userTs'] as $script) {
                ExtensionManagementUtility::addUserTSConfig($script);
            }
        }
        unset($this->registeredScripts['userTs']);
    }
    
    /**
     * Event handler to write the configured typoScript to static files and register them in typo3
     */
    public function __injectTypoScript()
    {
        // Mark as fired
        $this->tsEventFired = true;
        
        // Convert ts entries into tsFiles entries
        if (!empty($this->registeredScripts['ts'])) {
            foreach ($this->registeredScripts['ts'] as $id => $ts) {
                
                // Convert setup and constants from arrays to strings
                foreach (['setup', 'constants'] as $key) {
                    $tmp = '';
                    $title = $ts['title'];
                    foreach ($ts[$key] as $content) {
                        if (!empty($content)) {
                            $tmp .= "
[GLOBAL]
#############################################
## $title - START
#############################################

" . $content . "

#############################################
## $title - END
#############################################
[GLOBAL]
";
                        }
                    }
                    $ts[$key] = $tmp;
                }
                
                // Dump the files
                $dir = Inflector::toFile($id);
                $this->Fs->setFileContent($dir . '/constants.txt', $ts['constants']);
                $this->Fs->setFileContent($dir . '/setup.txt', $ts['setup']);
                $relPath = $this->Fs->getBaseDirectoryPath(true) . $dir;
                
                // Add the file to the tsFiles list
                $this->registeredScripts['tsFiles'][] = [$ts['extension'], $relPath, $ts['title']];
            }
            unset($this->registeredScripts['ts']);
        }
        
        // Register the ts files entries
        if (!empty($this->registeredScripts['tsFiles'])) {
            foreach ($this->registeredScripts['tsFiles'] as $file) {
                if (empty($file[2])) {
                    $file[2] = Inflector::toHuman($file[0]) . ' - Static TypoScript';
                }
                ExtensionManagementUtility::addStaticFile(...$file);
            }
        }
        
        unset($this->registeredScripts['tsFiles']);
    }
    
    /**
     * Internal helper which is called inside the setter methods to check
     * if the it is still possible to register new typoScript, or if the computation had already begun.
     *
     * If it is still possible to register typoScript we make sure to register ourselves to the required
     * events and inject our properly formatted ts into typo's "API"'s...
     *
     * @param bool $validateTsConfig If true we check for the ts config, if false we check for the typoScript
     *
     * @throws \LaborDigital\Typo3BetterApi\BetterApiException
     */
    protected function validateEventIsNotFired(bool $validateTsConfig = false)
    {
        // Ignore this in backend -> probably installing an extension...
        if ($this->TypoContext->getEnvAspect()->isBackend()) {
            return;
        }
        
        // Validate if the author is to late
        if ($validateTsConfig ? $this->tsConfigEventFired : $this->tsEventFired) {
            throw new BetterApiException('You can no longer add typoScript or ts config, because it was already processed by TYPO3');
        }
    }
    
    /**
     * Internal helper which is used to extract the requested $path's data from the given $config array
     *
     * @param array $config  The array to read the data from
     * @param mixed $path    The path to read from the config
     * @param array $options Additional config options
     *
     * @return array|mixed|null
     */
    protected function getPathHelper(array $config, $path, array $options = [])
    {
        $options = Options::make($options, [
            'default'   => null,
            'separator' => '.',
            'getType'   => false,
        ], ['ignoreUnknown' => true]);
        
        // Skip if we have no path
        if (empty($path)) {
            return $config;
        }
        
        // Prepare the path
        $path = Arrays::parsePath($path, $options['separator']);
        
        // Resolve the path until the last element
        $lastPathPart = rtrim(array_pop($path), '.');
        if (!empty($path)) {
            // Make path valid for typoScript lookups
            $path = array_map(function ($v) {
                // Remove tailing dots
                $v = rtrim($v, "\.");
                // Ignore wildcards
                if ($v === '*') {
                    return $v;
                }
                // Handle multi values
                if ($v[0] === '[') {
                    return str_replace(',', '.,', $v);
                }
                return $v . '.';
            }, $path);
            $config = Arrays::getPath($config, $path);
        }
        
        // Handle multi value last part
        if ($lastPathPart[0] === '[') {
            $lastPathPart = trim($lastPathPart, '[]');
            $result = [];
            foreach (array_map('trim', explode(',', $lastPathPart)) as $key) {
                if ($options['getType']) {
                    $result[$key] = isset($config[$key]) ? $config[$key] : $options['default'];
                } elseif (isset($config[$key . '.'])) {
                    $result[$key] = $config[$key . '.'];
                } elseif (isset($config[$key])) {
                    $result[$key] = $config[$key];
                } else {
                    $result[$key] = $options['default'];
                }
            }
            return $result;
        }
        
        // Find the last part
        if ($options['getType']) {
            return isset($config[$lastPathPart]) ? $config[$lastPathPart] : $options['default'];
        }
        if (isset($config[$lastPathPart . '.'])) {
            return $config[$lastPathPart . '.'];
        }
        if (isset($config[$lastPathPart])) {
            return $config[$lastPathPart];
        }
        return $options['default'];
    }
}
