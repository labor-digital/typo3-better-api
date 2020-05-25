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
 * Last modified: 2020.03.19 at 01:23
 */

namespace LaborDigital\Typo3BetterApi\Translation\FileSync;

use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\FileSystem\Fs;
use Neunerlei\Options\Options;
use Neunerlei\PathUtil\Path;

class TranslationFileSync
{
    
    /**
     * This method can be used to synchronize the translation files of a single extension.
     * It will take the base file (without lang prefix) as origin and makes sure that every language file (with prefix)
     * has every key the origin file has. If the language file does not have a key it will be removed automatically.
     *
     * If you rename a key(id) in the origin file, the script will try to update the matching keys in the
     * translation files if possible.
     *
     * @param string $extKey  The extension key of the extension to sync
     * @param array  $options Additional options. See syncFilesInDir() for the full list
     *
     * @see \LaborDigital\Typo3BetterApi\Translation\FileSync\TranslationFileSync::syncFilesInDir() for options
     */
    public static function syncFilesOf(string $extKey, array $options = [])
    {
        if (empty($options['productName'])) {
            $options['productName'] = $extKey;
        }
        static::syncFilesInDir(static::getContext()->getPathAspect()->getExtensionPath($extKey) . 'Resources/Private/Language/', $options);
    }
    
    /**
     * Similar to syncFilesOf() but does not synchronize a single extension, but a given
     * directory of translation .xlf files
     *
     * @param string $directory The directory path to synchronize the files in
     * @param array  $options   Additional options:
     *                          - productName STRING (REQUIRED) The product name for the translation file
     *                          - evenIfNotDev BOOL (default FALSE) By default this will only run in dev,
     *                          if you want to use it in prod or staging context, set this to TRUE.
     *                          - baseFallbackLang STRING (default "en") If the origin file (without lang prefix)
     *                          is missing, this will be used as language key for the newly created file
     *
     * @throws \LaborDigital\Typo3BetterApi\Translation\FileSync\TranslationFileSyncException
     */
    public static function syncFilesInDir(string $directory, array $options = [])
    {
        
        // Prepare options
        $options = Options::make($options, [
            'productName'      => [
                'type' => 'string',
            ],
            'evenIfNotDev'     => [
                'type'    => 'bool',
                'default' => false,
            ],
            'baseFallbackLang' => [
                'type'    => 'string',
                'default' => 'en',
            ],
        ]);
        
        // Get the container
        $container = TypoContainer::getInstance();
        
        // Return if we are not in a dev context
        if (!$options['evenIfNotDev'] && !static::getContext()->getEnvAspect()->isDev()) {
            return;
        }
        
        // Validate directory
        $directory = Path::unifyPath($directory);
        if (!is_dir($directory) || !is_readable($directory)) {
            throw new TranslationFileSyncException('The given directory: ' . $directory . ' is not readable for PHP!');
        }
        
        // Run the synchronizer
        $container->get(SyncHandler::class)
            ->sync(
                Fs::getDirectoryIterator($directory, false, ['fileRegex' => '~\\.xlf$~']),
                $options
            );
    }
    
    /**
     * Returns the context instance
     * @return \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
     */
    protected static function getContext(): TypoContext
    {
        return TypoContainer::getInstance()->get(TypoContext::class);
    }
}
