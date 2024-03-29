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

namespace LaborDigital\T3ba\Tool\TypoScript;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Core\Exception\T3baException;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Options\Options;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;


class TypoScriptService implements SingletonInterface, PublicServiceInterface
{
    use ContainerAwareTrait;
    use TypoContextAwareTrait;
    
    /**
     * @var \LaborDigital\T3ba\Tool\TypoScript\TypoScriptConfigurationManager
     */
    protected $configurationManager;
    
    /**
     * TypoScriptService constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\TypoScript\TypoScriptConfigurationManager  $configurationManager
     */
    public function __construct(TypoScriptConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }
    
    /**
     * This method can be used to retrieve typoScript constants from the template.
     *
     * @param   null|string|array  $path     Either a key or a path like "config.lang" to query the hierarchy. If left
     *                                       empty, the method will return the complete typoScript array.
     *
     * @param   array              $options  Additional options
     *                                       - default (mixed): By default the method returns null, if the queried value
     *                                       was not found in the configuration. If this option is set, the given value
     *                                       will be returned instead.
     *                                       - pid (integer): An optional pid to query the typoScript for.
     *                                       - separator (string) ".": A separator trough which the path parts are
     *                                       separated from each other
     *
     * @return mixed
     */
    public function getConstants($path = null, array $options = [])
    {
        $options = Options::make($options, [
            'default' => null,
            'pid' => $this->getPidOptionDefinition(),
            'separator' => '.',
        ]);
        
        // Load configuration
        if (! empty($options['pid'])) {
            $this->configurationManager->setCurrentPid($options['pid']);
        }
        $constants = $this->configurationManager->getTypoScriptConstants();
        if (! empty($options['pid'])) {
            $this->configurationManager->resetCurrentPid();
        }
        
        // Read contents
        return $this->getPathHelper($constants, $path, $options);
    }
    
    /**
     * This method can be used to retrieve typoScript setup from the template.
     *
     * @param   null|string|array  $path     Either a key or a path like "config.lang" to query the hierarchy. If left
     *                                       empty, the method will return the complete typoScript array.
     * @param   array              $options  Additional options
     *                                       - default (mixed): By default the method returns null, if the queried
     *                                       value was not found in the configuration. If this option is set, the given value
     *                                       will be returned instead.
     *                                       - pid (integer): An optional pid to query the typoScript for.
     *                                       - separator (string) ".": A separator trough which the path parts are
     *                                       separated from each other
     *                                       - getType (bool) FALSE: If set to TRUE the method will try return
     *                                       the typoScript object's type instead of it's value.
     *                                       The Type is normally stored as: key.key.type
     *                                       while the value is stored as: key.key.type. <- Note the period
     *                                       Not all elements have a type. If we don't fine one we will return the
     *                                       "default" value Otherwise we will try to get the value, and if not set
     *                                       return the type
     *
     * @return array|mixed|null
     */
    public function get($path = null, array $options = [])
    {
        $options = Options::make($options, [
            'default' => null,
            'pid' => $this->getPidOptionDefinition(),
            'separator' => '.',
            'getType' => false,
        ]);
        
        $this->configurationManager->setCurrentPid($options['pid']);
        $config = $this->configurationManager->getTypoScriptSetup();
        $this->configurationManager->resetCurrentPid();
        
        // Read contents
        return $this->getPathHelper($config, $path, $options);
    }
    
    /**
     * This method can be used to retrieve ts config values from the configuration.
     *
     * @param   null|string|array  $path     Either a key or a path like "mod.web_list" to query the hierarchy. If left
     *                                       empty, the method will return the complete typoScript array.
     * @param   array              $options  Additional options
     *                                       - default (mixed): By default the method returns null, if the queried
     *                                       value was not found in the configuration. If this option is set, the given
     *                                       value will be returned instead.
     *                                       - pid (integer): An optional pid to query the typoScript for.
     *                                       - separator (string) ".": A separator trough which the path parts are
     *                                       separated from each other
     *                                       - getType (bool) FALSE: If set to TRUE the method will try return
     *                                       the typoScript object's type instead of it's value.
     *                                       The Type is normally stored as: key.key.type
     *                                       while the value is stored as: key.key.type. <- Note the period
     *                                       Not all elements have a type. If we don't fine one we will return the
     *                                       "default" value Otherwise we will try to get the value, and if not set
     *                                       return the type
     *
     * @return array|mixed|null
     */
    public function getTsConfig($path = null, array $options = [])
    {
        $context = $this->getTypoContext();
        
        if ($context->env()->isFrontend() && $this->cs()->tsfe->hasTsfe()) {
            $tsConfig = $this->cs()->tsfe->getTsfe()->getPagesTSconfig();
        } else {
            $tsConfig = Arrays::merge(
                BackendUtility::getPagesTSconfig($this->getTypoContext()->pid()->getCurrent()),
                $context->beUser()->hasUser() ? $context->beUser()->getUser()->getTSConfig() : []
            );
        }
        
        $options = Options::make($options, [
            'default' => null,
            'pid' => $this->getPidOptionDefinition(),
            'separator' => '.',
            'getType' => false,
        ]);
        
        return $this->getPathHelper($tsConfig, $path, $options);
    }
    
    /**
     * Returns the plugin / extension configuration for ext base extensions
     *
     * @param   string|null  $extensionName  The extension name / key to read the configuration for
     * @param   string|null  $pluginName     Optional plugin to look up.
     *
     * @return array
     * @deprecated will be removed in v11 use TypoContext->config()->getExtBaseConfig() instead
     */
    public function getExtBaseSettings(?string $extensionName = null, ?string $pluginName = null): array
    {
        $cm = $this->getService(ConfigurationManagerInterface::class);
        $settings = $cm->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            $extensionName, $pluginName);
        
        return ! empty($settings) && is_array($settings) ?
            $settings : $cm->getConfiguration($extensionName, $pluginName);
    }
    
    /**
     * Parses the given typoScript configuration into an array and returns the result
     *
     * @param   string  $config
     *
     * @return array
     */
    public function parse(string $config): array
    {
        $parser = $this->makeInstance(TypoScriptParser::class);
        $parser->parse($config);
        
        return $parser->setup;
    }
    
    /**
     * Removes the tailing dot's from the given definition of parsed typoScript.
     *
     * @param   array|null  $config     The typoScript config to remove the dot's from
     * @param   bool        $keepTypes  By default the object types are moved into a \@type property of the child. If
     *                                  you don't want that set this to false. NOTE: In that case you will loose the
     *                                  types.
     *
     * @return array
     */
    public function removeDots(?array $config, bool $keepTypes = true): array
    {
        if (! is_array($config)) {
            return [];
        }
        
        $out = [];
        foreach ($config as $k => $v) {
            $keyWithoutDot = ! is_string($k) ? $k : rtrim($k, '.');
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
     * @param   string  $type    The content object name, eg. "TEXT" or "USER" or "IMAGE"
     * @param   array   $config  The array with TypoScript properties for the content object
     *
     * @return string
     */
    public function renderContentObject(string $type, array $config): string
    {
        return $this->cs()->simulator->runWithEnvironment([], function () use ($type, $config) {
            $result = $this->cs()->tsfe
                ->getContentObjectRenderer()
                ->cObjGetSingle($type, $config);
            
            if (is_string($result) && str_contains($result, '<!--INT_SCRIPT.')) {
                $tsfe = $this->cs()->tsfe->getTsfe();
                $contentBackup = $tsfe->content;
                $tsfe->content = $result;
                $tsfe->INTincScript();
                $result = $tsfe->content;
                $tsfe->content = $contentBackup;
                unset($contentBackup);
            }
            
            return $result;
        });
    }
    
    /**
     * Renders an existing content element, based on the configuration set via typoScript.
     *
     * @param   string|array  $selector  The access path where to find the content element in typoScript
     *
     * @return string
     * @throws T3baException
     */
    public function renderContentObjectWith($selector): string
    {
        $type = $this->get($selector, ['getType']);
        $config = $this->get($selector);
        if (empty($type) || empty($config)) {
            throw new T3baException("The given selector $selector is not a valid cObject");
        }
        
        return $this->renderContentObject($type, $config);
    }
    
    /**
     * Allows you to process a text using the lib.parseFunc definition.
     *
     * A shortcut to the {@link ContentObjectRenderer::parseFunc()}, which states:
     * Implements the stdWrap property, "parseFunc".
     *
     * This is a function with a lot of interesting uses. In classic TypoScript this is used to process text
     * from the bodytext field; This included highlighting of search words, changing http:// and mailto: prefixed strings into etc.
     * It is still a very important function for processing of bodytext which is normally stored in the database
     * in a format which is not fully ready to be outputted.
     * This situation has not become better by having an RTE around...
     *
     * @param   string       $text             The text to be parsed by the content object
     * @param   string|null  $parseFuncTSPath  The Ts configuration path, by default: lib.parseFunc_RTE
     *
     * @return string
     */
    public function applyTextParseFunc(string $text, ?string $parseFuncTSPath = null): string
    {
        $parseFuncTSPath = $parseFuncTSPath ?? 'lib.parseFunc_RTE';
        
        return $this->cs()->simulator->runWithEnvironment([], function () use ($text, $parseFuncTSPath) {
            $cObj = $this->makeInstance(ContentObjectRenderer::class);
            $cObj->start([]);
            
            return $cObj->parseFunc($text, [], '< ' . $parseFuncTSPath);
        });
    }
    
    /**
     * Returns the option definition for the pid option
     *
     * @return array
     */
    protected function getPidOptionDefinition(): array
    {
        return [
            'default' => null,
            'type' => ['int', 'string', 'null'],
            'filter' => function ($v) {
                if (is_int($v)) {
                    return $v;
                }
                
                if ($v === null) {
                    return $this->getTypoContext()->pid()->getCurrent();
                }
                
                if (is_numeric($v)) {
                    return (int)$v;
                }
                
                return $this->getTypoContext()->pid()->get($v);
                
            },
        ];
    }
    
    /**
     * Internal helper which is used to extract the requested $path's data from the given $config array
     *
     * @param   array  $config   The array to read the data from
     * @param   mixed  $path     The path to read from the config
     * @param   array  $options  Additional config options
     *
     * @return array|mixed|null
     * @noinspection UnSafeIsSetOverArrayInspection
     */
    protected function getPathHelper(array $config, $path, array $options = [])
    {
        $options = Options::make($options, [
            'default' => null,
            'separator' => '.',
            'getType' => false,
        ], ['ignoreUnknown' => true]);
        
        // Skip if we have no path
        if (empty($path)) {
            return $config;
        }
        
        // Prepare the path
        $path = Arrays::parsePath($path, $options['separator']);
        
        // Resolve the path until the last element
        $lastPathPart = rtrim(array_pop($path), '.');
        if (! empty($path)) {
            // Make path valid for typoScript lookups
            $path = array_map(static function ($v) {
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
            $config = Arrays::getPath($config, $path) ?? [];
        }
        
        // Handle multi value last part
        if ($lastPathPart[0] === '[') {
            $lastPathPart = trim($lastPathPart, '[]');
            $result = [];
            foreach (array_map('trim', explode(',', $lastPathPart)) as $key) {
                if ($options['getType']) {
                    $result[$key] = $config[$key] ?? $options['default'];
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
            return $config[$lastPathPart] ?? $options['default'];
        }
        
        return $config[$lastPathPart . '.'] ?? $config[$lastPathPart] ?? $options['default'];
    }
}
