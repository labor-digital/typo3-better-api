<?php
/*
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
 * Last modified: 2020.08.25 at 11:03
 */

declare(strict_types=1);


namespace LaborDigital\Typo3BetterApi\TypoScript\DynamicTypoScript;


use LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs;
use Neunerlei\Inflection\Inflector;
use SplFileInfo;
use TYPO3\CMS\Core\SingletonInterface;

class DynamicTypoScriptRegistry implements SingletonInterface
{
    /**
     * @var TempFs
     */
    protected $fs;

    /**
     * The contents that have been collected
     *
     * @var array
     */
    protected $contents = [];

    /**
     * DynamicTypoScriptRegistry constructor.
     */
    public function __construct()
    {
        $this->fs       = TempFs::makeInstance('DynamicTypoScript');
        $memory         = $this->fs->hasFile('memory') ? $this->fs->getFileContent('memory') : [];
        $this->contents = $memory;
    }

    /**
     * Returns true if the memory hack exists, false if not
     *
     * @return bool
     */
    public function hasMemory(): bool
    {
        return $this->fs->hasFile('memory');
    }

    /**
     * Adds a new snippet of dynamic typo script to the registry.
     * Dynamic typoScript can be included into virtually any typoScript or tsConfig file using
     * the (at)import statement.
     *
     * For example: You add a snippet with key: "myKey" and the content "config.test = 123"
     *
     * In your real typo script file you can now include the dynamic content with (at)import "dynamic:myKey"
     * and with that your configuration will be loaded from the dynamic storage instead.
     *
     * NOTE: If a key already exists your content will be appended to it.
     *
     * @param   string  $key      A unique definition key to add the dynamic content with
     * @param   string  $content  The typoScript configuration to add for the key
     *
     * @return $this
     */
    public function addContent(string $key, string $content): self
    {
        if (! isset($this->contents[$key])) {
            $this->contents[$key] = '';
        }

        $this->contents[$key] .= '
[GLOBAL]
#############################################
' . $content . '
#############################################
[GLOBAL]
';

        return $this;
    }

    /**
     * A dirty hack to simulate the config memorization of v10 in v9
     *
     * @param   string  $key
     * @param   string  $content
     *
     * @return $this
     */
    public function memorizeContent(string $key, string $content): self
    {
        $this->addContent($key, $content);
        $memory       = $this->fs->hasFile('memory') ? $this->fs->getFileContent('memory') : [];
        $memory[$key] = $content;
        $this->fs->setFileContent('memory', $memory);

        return $this;
    }

    /**
     * Returns all collected contents for a definition key.
     * It will return a speaking comment if there were no contents for this key given
     *
     * @param   string  $key  The definition key to return the contents for
     *
     * @return string
     */
    public function getContents(string $key): string
    {
        if (! isset($this->contents[$key])) {
            return '
[GLOBAL]
#############################################
# "' . $key . '" has no registered,
# dynamic typoScript definition
#############################################
[GLOBAL]
';
        }

        return $this->contents[$key];
    }


    /**
     * Dumps the typoScript definition into a file on the temp fs
     * and returns the file information object for it
     *
     * @param   string  $key  The definition key to dump into a file
     *
     * @return \SplFileInfo
     */
    public function getFile(string $key): SplFileInfo
    {
        $content  = $this->getContents($key);
        $realName = substr(Inflector::toFile($key), 0, 50) . '-' . md5($key . $content) . '.typoscript';
        if (! $this->fs->hasFile($realName)) {
            $this->fs->setFileContent($realName, $content);
        }

        return $this->fs->getFile($realName);
    }
}
