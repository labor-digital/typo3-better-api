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

use Iterator;
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\FileAndFolder\Permissions;
use Neunerlei\Arrays\Arrays;
use Neunerlei\FileSystem\Fs;
use Neunerlei\TinyTimy\DateTimy;
use SplFileInfo;
use TYPO3\CMS\Core\SingletonInterface;

class SyncHandler implements SingletonInterface
{
    
    /**
     * @var \LaborDigital\Typo3BetterApi\Container\TypoContainer
     */
    protected $container;
    
    /**
     * SyncHandler constructor.
     *
     * @param \LaborDigital\Typo3BetterApi\Container\TypoContainer $container
     */
    public function __construct(TypoContainer $container)
    {
        $this->container = $container;
    }
    
    /**
     * Receives the directory iterator of the target directory, containing only .xlf files to sync
     *
     * @param \Iterator $iterator The directory iterator
     * @param array     $options  Options inherited from TranslationFileSync::syncFilesInDir()
     */
    public function sync(Iterator $iterator, array $options)
    {
        
        // Load the filesets
        $fileSets = [];
        $languages = [];
        foreach ($iterator as $k => $v) {
            if (!$v instanceof SplFileInfo) {
                continue;
            }
            $basename = $v->getBasename('.xlf');
            $langKey = '';
            if (stripos($basename, '.') === 2) {
                $langKey = substr($basename, 0, 2);
                $languages[$langKey] = $langKey;
                $basename = substr($basename, 3);
            }
            
            // Make sure we have a fileset
            if (!isset($fileSets[$basename])) {
                $fileSets[$basename] = [
                    'baseFile'  => '',
                    'langFiles' => [],
                ];
            }
            
            // Add file to list
            if (empty($langKey)) {
                $fileSets[$basename]['baseFile'] = $v->getPathname();
            } else {
                $fileSets[$basename]['langFiles'][$langKey] = $v->getPathname();
            }
        }
        
        // Synchronize the sets separate from each other
        foreach ($fileSets as $fileSet) {
            $this->syncFileSet($fileSet, $languages, $options);
        }
    }
    
    /**
     * Handles the synchronization of a single file set, or namespace if you will.
     *
     * @param array $fileSet   The definition of the set to synchronize
     * @param array $languages The language keys of all languages (over all fileSets)
     * @param array $options   Options inherited from TranslationFileSync::syncFilesInDir()
     */
    protected function syncFileSet(array $fileSet, array $languages, array $options)
    {
        // Make sure we have a base file
        if (empty($fileSet['baseFile'])) {
            
            // Load the first file
            $fallbackFile = $this->loadFile(reset($fileSet['langFiles']));
            
            // Update the file name
            $fallbackFile->filename = dirname($fallbackFile->filename) . DIRECTORY_SEPARATOR .
                substr(basename($fallbackFile->filename), stripos(basename($fallbackFile->filename), '.') + 1);
            
            // Reset it to english
            $fallbackFile->targetLang = null;
            $fallbackFile->sourceLang = $options['baseFallbackLang'];
            $fallbackFile->productName = $options['productName'];
            
            // Clear target for all messages
            foreach ($fallbackFile->messages as $k => $message) {
                $message->target = null;
            }
            
            // Done
            $baseFile = $fallbackFile;
        } else {
            // Load the base file
            $baseFile = $this->loadFile($fileSet['baseFile'], null, $options['baseFallbackLang'], $options['productName']);
        }
        
        // Make sure we have language files for each registered language
        if (count($languages) !== count($fileSet['langFiles'])) {
            foreach ($languages as $language => $foo) {
                if (isset($fileSet['langFiles'][$language])) {
                    continue;
                }
                $fileSet['langFiles'][$language] =
                    dirname($fileSet['baseFile']) . DIRECTORY_SEPARATOR . $language . '.' . basename($fileSet['baseFile']);
                touch($fileSet['langFiles'][$language]);
            }
        }
        
        // Create mapping
        $map = $this->container->get(TranslationMapping::class);
        $map->setBaseFile($baseFile);
        
        // Load all language files
        foreach ($fileSet['langFiles'] as $lang => $filename) {
            $langFile = $this->loadFile($filename, $lang, $baseFile->sourceLang, $baseFile->productName);
            $map->addTranslationFile($langFile);
        }
        
        // Synchronize the languages
        $map->synchronize();
        
        // Write files again
        $this->dumpFile($baseFile, true);
        foreach ($map->getTranslationFiles() as $file) {
            $this->dumpFile($file, false);
        }
    }
    
    /**
     * Loads a single translation .xlf file into the TranslationFile object representation
     *
     * @param string      $filename         The filename of the file to load
     * @param string|null $languageFallback Optional language key to use as target-language attribute if it is missing
     * @param string|null $baseLanguage     Optional language key to use as source-language if it is missing
     * @param string|null $productName      Optional product name to set if it is missing
     *
     * @return \LaborDigital\Typo3BetterApi\Translation\FileSync\TranslationFile
     */
    protected function loadFile(
        string $filename,
        ?string $languageFallback = null,
        ?string $baseLanguage = null,
        ?string $productName = null
    ): TranslationFile
    {
        $content = Fs::readFile($filename);
        $contentList = Arrays::makeFromXml($content);
        
        // Read the file metadata
        $file = $this->container->get(TranslationFile::class);
        $file->filename = $filename;
        foreach (Arrays::getPath($contentList, '0.0.*', []) as $k => $row) {
            if (is_string($k) && $k[0] === '@') {
                $file->params[$k] = $row;
            }
        }
        $file->sourceLang = Arrays::getPath($contentList, '0.0.@source-language', $baseLanguage);
        $file->productName = Arrays::getPath($contentList, '0.0.@product-name', $productName);
        $file->targetLang = Arrays::getPath($contentList, '0.0.@target-language', $languageFallback);
        
        // Read the messages
        foreach (Arrays::getPath($contentList, '0.0.*', []) as $entry) {
            if (!isset($entry['tag']) || $entry['tag'] !== 'body') {
                continue;
            }
            foreach ($entry as $k => $row) {
                // Ignore body tag
                if ($k === 'tag') {
                    continue;
                }
                // Ignore attributes
                if (is_string($k)) {
                    continue;
                }
                // Ignore invalid elements
                if (!isset($row['@id'])) {
                    continue;
                }
                
                // Create a new item/unit
                $item = $this->container->get(TranslationFileUnit::class);
                $item->id = $row['@id'];
                $hasError = false;
                
                
                // Save notes
                if ($row['tag'] === 'note') {
                    $item->isNote = true;
                    
                    // Unify line breaks
                    $row['content'] = str_replace(["\t", "\r\n", PHP_EOL], PHP_EOL, $row['content']);
                    
                    $item->note = isset($row['content']) ?
                        implode(PHP_EOL, array_filter(array_map('trim', explode(PHP_EOL, $row['content'])))) : '';
                } // Save translation units
                elseif ($row['tag'] === 'trans-unit') {
                    foreach ($row as $_k => $child) {
                        if (is_string($_k) || !is_array($child)) {
                            continue;
                        }
                        
                        // Unify line breaks
                        $child['content'] = str_replace(["\t", "\r\n", PHP_EOL], PHP_EOL, $child['content']);
                        $child['content'] = implode(' ', array_filter(array_map('trim', explode(PHP_EOL, $child['content']))));
                        
                        // Load the content
                        switch (Arrays::getPath($child, 'tag')) {
                            case 'source':
                                $item->source = $child['content'];
                                break;
                            case 'target':
                                $item->target = $child['content'];
                                break;
                            default:
                                $hasError = true;
                                break;
                        }
                    }
                }
                
                // Ignore on error
                if ($hasError) {
                    continue;
                }
                $file->messages[$item->id] = $item;
            }
        }
        
        // Done
        return $file;
    }
    
    /**
     * Dumps a object representation of TranslationFile into the xml .xlf file format
     *
     * @param \LaborDigital\Typo3BetterApi\Translation\FileSync\TranslationFile $file       The object to dump to a
     *                                                                                      file
     * @param bool                                                              $isBaseFile True if this is the
     *                                                                                      base/origin file
     */
    protected function dumpFile(TranslationFile $file, bool $isBaseFile)
    {
        
        // Build a list of children
        $children = [
            'tag' => 'body',
        ];
        foreach ($file->messages as $message) {
            if ($message->isNote) {
                $children[] = [
                    'tag'     => 'note',
                    '@id'     => $message->id,
                    'content' => PHP_EOL . $message->note . PHP_EOL,
                ];
            } else {
                $children[] =
                    Arrays::attach(
                        [
                        'tag' => 'trans-unit',
                        '@id' => $message->id,
                        [
                            'tag'     => 'source',
                            'content' => $message->source,
                        ],
                    ],
                        $isBaseFile ? [] : [1 => [
                        'tag'     => 'target',
                        'content' => $message->target,
                    ],
                    ]
                    );
            }
        }
        
        // Create an array representation for this file
        $out = [
            [
                'tag'      => 'xliff',
                '@version' => '1.0',
                Arrays::attach([
                    'tag'              => 'file',
                    '@source-language' => $file->sourceLang,
                    '@datatype'        => 'plaintext',
                    '@original'        => 'messages',
                    '@date'            => (new DateTimy())->format("Y-m-d\TH:i:s\Z"),
                    '@product-name'    => $file->productName,
                    [
                        'tag'     => 'header',
                        'content' => '',
                    ],
                    $children,
                ], $isBaseFile ? [] : ['@target-language' => $file->targetLang,]),
            ],
        ];
        
        // Dump the file
        $xml = Arrays::dumpToXml($out, true);
        Fs::writeFile($file->filename, $xml);
        Permissions::setFilePermissions($file->filename);
    }
}
