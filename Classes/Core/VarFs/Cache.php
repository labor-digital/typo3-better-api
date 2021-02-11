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
 * Last modified: 2020.08.23 at 15:55
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\VarFs;


use DateInterval;
use Neunerlei\Inflection\Inflector;
use Neunerlei\TinyTimy\DateTimy;
use Psr\SimpleCache\CacheInterface;

class Cache implements CacheInterface
{
    /**
     * @var \LaborDigital\T3BA\Core\VarFs\Mount
     */
    protected $mount;

    /**
     * Cache constructor.
     *
     * @param   \LaborDigital\T3BA\Core\VarFs\Mount  $mount
     */
    public function __construct(Mount $mount)
    {
        $this->mount = $mount;
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        if (! $this->has($key)) {
            return $default;
        }

        return $this->mount->getFileContent($this->keyToCacheFilename($key));
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        $file = $this->keyToCacheFilename($key);
        $this->mount->setFileContent($file, $value);

        if ($ttl !== null) {
            if ($ttl instanceof DateInterval) {
                $endTime = new DateTimy();
                $endTime->add($ttl);
                $ttl = ($endTime)->getTimestamp() - (new DateTimy('now'))->getTimestamp();
                $ttl = (int)abs($ttl);
            }
            $this->mount->setFileContent($file . '.ttl', (new DateTimy($ttl . ' seconds from now')));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        $this->mount->delete($this->keyToCacheFilename($key));
        $this->mount->delete($this->keyToCacheFilename($key) . '.ttl');

        return true;
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->mount->flush();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        $result = true;
        foreach ($values as $k => $v) {
            $result = $result && $this->set($k, $v, $ttl);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        $result = true;
        foreach ($keys as $key) {
            $result = $result && $this->delete($key);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        $file = $this->keyToCacheFilename($key);
        if (! $this->mount->hasFile($file)) {
            return false;
        }

        $ttlFile = $file . '.ttl';
        if (! $this->mount->hasFile($ttlFile)) {
            return true;
        }

        $ttl = $this->mount->getFileContent($ttlFile);
        if (! $ttl instanceof DateTimy) {
            return false;
        }
        if ($ttl < new DateTimy()) {
            return false;
        }

        return true;
    }

    /**
     * Converts a key into a cache file name
     *
     * @param $key
     *
     * @return string
     */
    protected function keyToCacheFilename($key): string
    {
        $hash         = md5((string)$key);
        $sanitizedKey = substr(Inflector::toFile($key), 0, 50) . '-' . $hash;

        return 'Cache/' . $hash[0] . '/' . $hash[1] . '/' . $hash[2] . '/' . $sanitizedKey;
    }
}
