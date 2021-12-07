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

namespace LaborDigital\T3ba\Core\VarFs;

use LaborDigital\T3ba\Core\Util\FilePermissionUtil;
use LaborDigital\T3ba\Core\VarFs\Exception\InvalidRootPathException;
use Neunerlei\FileSystem\Fs;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;
use Psr\SimpleCache\CacheInterface;

/**
 * Class VarFs
 *
 * In earlier versions I used the caching framework extensively when it came
 * to storing dynamically generated content. However, it is no longer allowed
 * to create caches while the ext_localconf and TCA files are generated.
 *
 * Therefore, all data that is dynamically generated is now stored in a separate
 * temporary directory tree, which is abstracted by this class.
 *
 * @package LaborDigital\T3ba\Core\VarFs
 */
class VarFs
{
    
    /**
     * The base directory where the file system will work in
     *
     * @var string
     */
    protected $rootPath;
    
    /**
     * @var CacheInterface
     */
    protected $cache;
    
    /**
     * The list of all loaded mounts
     *
     * @var \LaborDigital\T3ba\Core\VarFs\Mount[]
     */
    protected $mounts = [];
    
    /**
     * VarFs constructor.
     *
     * @throws \LaborDigital\T3ba\Core\VarFs\Exception\InvalidRootPathException
     */
    public function __construct()
    {
        $this->rootPath = Path::unifyPath(BETTER_API_TYPO3_VAR_PATH, '/') . 't3ba/';
        
        if (is_file($this->rootPath)) {
            throw new InvalidRootPathException(
                'The resolved root directory path: "' . $this->rootPath . '" seems to lead to a file!');
        }
        
        if (! is_writable($this->rootPath)) {
            Fs::mkdir($this->rootPath);
            FilePermissionUtil::setFilePermissions($this->rootPath);
        }
        
        if (! is_writable($this->rootPath) && ! is_writable(dirname($this->rootPath))) {
            throw new InvalidRootPathException(
                'The resolved root directory path: "' . $this->rootPath . '" is not writable by the web-server!');
        }
    }
    
    /**
     * Completely removes all files and directories inside the file system
     */
    public function flush(): void
    {
        $this->mounts = [];
        Fs::flushDirectory($this->rootPath);
    }
    
    /**
     * Returns the reference to a single mount inside the file system.
     * A mount is basically a single directory where you can read and write files in.
     *
     * @param   string  $id  A unique id for the mount to retrieve
     *
     * @return \LaborDigital\T3ba\Core\VarFs\Mount
     */
    public function getMount(string $id): Mount
    {
        $mountName = Inflector::toCamelCase(Inflector::toFile(Inflector::toDashed($id)));
        
        return $this->mounts[$mountName] ?? $this->mounts[$mountName]
                = new Mount(Path::join($this->rootPath, $mountName));
    }
    
    /**
     * Returns a cache implementation which stores it's values inside this filesystem
     *
     * @return \Psr\SimpleCache\CacheInterface
     */
    public function getCache(): CacheInterface
    {
        return $this->cache ?? ($this->cache = new Cache($this->getMount('cache')));
    }
}
