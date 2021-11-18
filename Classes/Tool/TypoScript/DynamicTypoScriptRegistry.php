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


use LaborDigital\T3ba\Core\VarFs\VarFs;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\Configuration\State\LocallyCachedStatePropertyTrait;
use Neunerlei\Inflection\Inflector;
use SplFileInfo;
use TYPO3\CMS\Core\SingletonInterface;

class DynamicTypoScriptRegistry implements SingletonInterface
{
    use LocallyCachedStatePropertyTrait;
    
    /**
     * @var \LaborDigital\T3ba\Core\VarFs\VarFs
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
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $configState
     * @param   \LaborDigital\T3ba\Core\VarFs\VarFs         $fs
     */
    public function __construct(ConfigState $configState, VarFs $fs)
    {
        $this->fs = $fs->getMount('DynamicTypoScript');
        $this->registerCachedProperty('contents', 'typo.typoScript.dynamicTypoScript', $configState, null, []);
    }
    
    /**
     * Adds a new snippet of dynamic typo script to the registry.
     * Dynamic typoScript can be included into virtually any typoScript or tsConfig file using
     * the @import statement.
     *
     * For example: You add a snippet with key: "myKey" and the content "config.test = 123"
     *
     * In your real typo script file you can now include the dynamic content with @import "dynamic:myKey"
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
        $content = $this->getContents($key);
        $realName = substr(Inflector::toFile($key), 0, 50) . '-' . md5($key . $content) . '.typoscript';
        if (! $this->fs->hasFile($realName)) {
            $this->fs->setFileContent($realName, $content);
        }
        
        return $this->fs->getFile($realName);
    }
}
