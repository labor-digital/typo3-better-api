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
 * Last modified: 2021.11.18 at 13:16
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Icon;


use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfig\ExtConfigException;
use Neunerlei\Configuration\State\LocallyCachedStatePropertyTrait;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Core\Imaging\IconProvider\AbstractSvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProviderInterface;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\SingletonInterface;

class ExtConfigIconRegistry implements PublicServiceInterface, SingletonInterface
{
    use LocallyCachedStatePropertyTrait;
    
    /**
     * @var \TYPO3\CMS\Core\Imaging\IconRegistry
     */
    protected $iconRegistry;
    
    /**
     * @var \LaborDigital\T3ba\ExtConfig\ExtConfigContext
     */
    protected $context;
    
    /**
     * The list of resolved icons as inherited from the config state object
     *
     * @var array
     */
    protected $icons;
    
    /**
     * A list of icon identifiers that were registered through this class
     *
     * @var array
     */
    protected $registeredIcons = [];
    
    public function __construct(IconRegistry $iconRegistry, ExtConfigContext $context)
    {
        $this->iconRegistry = $iconRegistry;
        $this->context = $context;
        $this->registerCachedProperty('icons', 'typo.icon.icons', $context->getState());
    }
    
    /**
     * Generates a unique identifier for a given filename
     * If the filename was already registered by another icon, the existing icon identifier will be returned.
     *
     * @param   string  $filename
     *
     * @return string
     */
    public function getIdentifierForFilename(string $filename): string
    {
        $filename = $this->context->resolveFilename($filename);
        
        if (in_array($filename, $this->icons, true)) {
            return array_search($filename, $this->icons, true);
        }
        
        // If the filename does not look like a filename we simply return it -> this is probably an identifier already
        if (! str_contains($filename, '.') || ! str_contains($filename, '/')) {
            return $filename;
        }
        
        $identifier = 't3ba-' . basename($filename, pathinfo($filename, PATHINFO_EXTENSION));
        $identifier .= '-' . md5($filename);
        
        return Inflector::toFile($identifier);
    }
    
    /**
     * Resolves the filename of an icon based on its identifier
     * If there is no matching filename, the given identifier will be returned instead.
     *
     * @param   string  $identifier  The identifier to resolve the matching filename for.
     *
     * @return string
     */
    public function getFilenameForIcon(string $identifier): string
    {
        if (isset($this->registeredIcons[$identifier])) {
            return $this->registeredIcons[$identifier];
        }
        
        if ($this->iconRegistry->isRegistered($identifier)) {
            $config = $this->iconRegistry->getIconConfigurationByIdentifier($identifier);
            
            return $config['source'] ?? $identifier;
        }
        
        return $identifier;
    }
    
    /**
     * Registers a new icon in the TYPO3 icon registry
     *
     * @param   string       $identifier         The unique identifier to register the icon with. Should start with the extension key
     * @param   string       $filename           The filename of the icon to display. Can be either an ABSOLUTE path, a RELATIVE path (starting with ./) or a path
     *                                           that begins with EXT:ExtKey... in order to be resolved.
     * @param   string|null  $providerClass      Optional provider class for the icon. The provider MUST implement the IconProviderInterface.
     *                                           If NULL or omitted, the provider class will be automatically resolved based on the filenames extension
     * @param   array|null   $additionalOptions  Additional options to pass to the icon provider
     *
     * @return $this
     * @see IconProviderInterface
     */
    public function registerIcon(
        string $identifier,
        string $filename,
        ?string &$providerClass = null,
        ?array $additionalOptions = null
    ): self
    {
        $identifier = $this->context->replaceMarkers($identifier);
        $filename = $this->context->resolveFilename($filename);
        $providerClass = $providerClass ?? $this->iconRegistry->detectIconProvider($filename);
        $options = $additionalOptions ?? [];
        $options['source'] = $filename;
        
        return $this->registerIconRaw($identifier, $providerClass, $options);
    }
    
    /**
     * Registers a "raw" icon configuration, which is passed directly into the {@link IconRegistry::registerIcon()} method of the
     * TYPO3 icon registry. It allows you to register an icon, like it is defined in the Icon API definition in the docs.
     *
     * @param   string  $identifier
     * @param   string  $providerClass
     * @param   array   $options
     *
     * @return $this
     * @throws \LaborDigital\T3ba\ExtConfig\ExtConfigException
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Icon/Index.html
     */
    public function registerIconRaw(
        string $identifier,
        string $providerClass,
        array $options
    ): self
    {
        if (! in_array(IconProviderInterface::class, class_implements($providerClass), true)) {
            throw new ExtConfigException(
                'Invalid IconProvider: "' . $providerClass .
                '" given, a provider must implement the required interface: "' . IconProviderInterface::class . '"');
        }
        
        $providerParents = class_parents($providerClass);
        if (in_array(AbstractSvgIconProvider::class, $providerParents, true)
            || in_array(BitmapIconProvider::class, $providerParents, true)) {
            $this->registeredIcons[$identifier] = $this->context->resolveFilename($options['source']);
        }
        
        $this->iconRegistry->registerIcon($identifier, $providerClass, $options);
        $this->context->getState()->set('typo.icon.icons.' . $identifier, [$identifier, $providerClass, $options]);
        
        return $this;
    }
    
    /**
     * Registers a new icon alias in the registry. This allows you to resolve an icon through another name
     *
     * @param   string  $alias       The alias identifier that should point to the real $identifier
     * @param   string  $identifier  The identifier that should be available as $alias
     *
     * @return $this
     * @see IconRegistry::registerAlias()
     */
    public function registerAlias(string $alias, string $identifier): self
    {
        if ($this->iconRegistry->isRegistered($identifier)) {
            $this->iconRegistry->registerAlias($alias, $identifier);
        }
        
        $this->context->getState()->set('typo.icon.aliases.' . $identifier, [$alias, $identifier]);
        
        return $this;
    }
    
    /**
     * Registers an icon with a provided identifier as selectable in the "pages" TCA under the "module" field.
     * This allows you to create module icons without juggling with the TCA at all.
     *
     * @param   string       $tableNameOrType  Either a table name or a type to use as unique flag
     * @param   string       $identifier       The icon identifier to be registered. The icon file must be registered in the system.
     * @param   string|null  $label            A label to be shown for the icon. If omitted and $tableNameOrType is a table name,
     *                                         the table name will be used as label, otherwise a "humanized" version of the type string will be used.
     *
     * @return $this
     */
    public function registerPageModuleIcon(string $tableNameOrType, string $identifier, ?string $label = null): self
    {
        $this->context->getState()->attachToArray(
            'typo.icon.pages.module',
            [$tableNameOrType, $identifier, $label]
        );
        
        return $this;
    }
}