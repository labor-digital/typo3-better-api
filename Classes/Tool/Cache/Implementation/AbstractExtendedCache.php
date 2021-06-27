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


namespace LaborDigital\T3ba\Tool\Cache\Implementation;


use Closure;
use LaborDigital\T3ba\Tool\Cache\CacheInterface;
use LaborDigital\T3ba\Tool\Cache\InvalidArgumentException;
use LaborDigital\T3ba\Tool\Cache\KeyGenerator\ArrayBasedCacheKeyGenerator;
use LaborDigital\T3ba\Tool\Cache\KeyGenerator\CacheKeyGeneratorInterface;
use LaborDigital\T3ba\Tool\Cache\KeyGenerator\CallableCacheKeyGenerator;
use LaborDigital\T3ba\Tool\Cache\Util\CacheUtil;
use Neunerlei\Inflection\Inflector;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Cache\Backend\BackendInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractExtendedCache implements FrontendInterface, CacheInterface
{
    /**
     * @var FrontendInterface
     */
    protected $concreteFrontend;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Cache\KeyGenerator\CacheKeyGeneratorInterface
     */
    protected $environmentCacheKeyGenerator;
    
    /**
     * This is a list of keys that have been set and therefore are able to access the cached value.
     * This allows us to block all cache requests when an "update" is requested, until a key was set,
     * after that the cached value will be returned.
     *
     * @var array
     */
    protected $keysThatWereSet = [];
    
    
    public function __construct(
        FrontendInterface $concreteFrontend,
        CacheKeyGeneratorInterface $environmentCacheKeyGenerator
    )
    {
        $this->concreteFrontend = $concreteFrontend;
        $this->environmentCacheKeyGenerator = $environmentCacheKeyGenerator;
    }
    
    /**
     * @inheritDoc
     */
    public function delete($key): bool
    {
        return $this->remove($key);
    }
    
    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $this->flush();
        
        return true;
    }
    
    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        if (! is_iterable($keys)) {
            throw new InvalidArgumentException('The given $keys are not iterable');
        }
        
        foreach ($keys as $key) {
            yield $this->get($key, $default);
        }
    }
    
    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        if (! is_iterable($values)) {
            throw new InvalidArgumentException('The given $values are not iterable');
        }
        
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
    }
    
    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys): bool
    {
        if (! is_iterable($keys)) {
            throw new InvalidArgumentException('The given $keys are not iterable');
        }
        
        $result = true;
        foreach ($keys as $key) {
            $result = $result && $this->delete($key);
        }
        
        return $result;
    }
    
    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return $this->concreteFrontend->getIdentifier();
    }
    
    /**
     * @inheritDoc
     */
    public function getFrontend(): FrontendInterface
    {
        return $this->concreteFrontend;
    }
    
    /**
     * @inheritDoc
     */
    public function getBackend(): BackendInterface
    {
        return $this->concreteFrontend->getBackend();
    }
    
    /**
     * @inheritDoc
     */
    public function set($entryIdentifier, $data, $tagsOrLifetime = null, $lifetime = null): bool
    {
        try {
            $tags = [];
            if (is_array($tagsOrLifetime)) {
                $tags = $tagsOrLifetime;
            } elseif (is_int($tagsOrLifetime)) {
                $lifetime = $tagsOrLifetime;
            }
            
            $res = $this->concreteFrontend->set(
                $this->prepareIdentifier($entryIdentifier),
                $data,
                $this->prepareTags($tags),
                $lifetime
            );
            
            return $res ?? true;
        } catch (\InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * @inheritDoc
     */
    public function get($entryIdentifier, $default = null)
    {
        if ($this->isUpdate() && ! in_array($entryIdentifier, $this->keysThatWereSet, true)) {
            return $default;
        }
        
        $entryIdentifier = $this->prepareIdentifier($entryIdentifier);
        if ($this->concreteFrontend->has($entryIdentifier)) {
            return $this->concreteFrontend->get($entryIdentifier);
        }
        
        return $default;
    }
    
    /**
     * @inheritDoc
     */
    public function has($entryIdentifier): bool
    {
        if ($this->isUpdate() && ! in_array($entryIdentifier, $this->keysThatWereSet, true)) {
            return false;
        }
        
        try {
            return $this->concreteFrontend->has($this->prepareIdentifier($entryIdentifier));
        } catch (\InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * @inheritDoc
     */
    public function remove($entryIdentifier): bool
    {
        try {
            return $this->concreteFrontend->remove($this->prepareIdentifier($entryIdentifier));
        } catch (\InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * @inheritDoc
     */
    public function flush(): bool
    {
        $this->concreteFrontend->flush();
        
        return true;
    }
    
    /**
     * @inheritDoc
     */
    public function flushByTag($tag): bool
    {
        return $this->flushByTags([$tag]);
    }
    
    /**
     * @inheritDoc
     */
    public function flushByTags(array $tags): bool
    {
        try {
            $this->concreteFrontend->flushByTags($this->prepareTags($tags));
            
            return true;
        } catch (\InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * @inheritDoc
     */
    public function collectGarbage()
    {
        return $this->concreteFrontend->collectGarbage();
    }
    
    /**
     * @inheritDoc
     */
    public function isValidEntryIdentifier($identifier): bool
    {
        return $this->concreteFrontend->isValidEntryIdentifier($this->prepareIdentifier($identifier));
    }
    
    /**
     * @inheritDoc
     */
    public function isValidTag($tag): bool
    {
        $valid = true;
        foreach ($this->prepareTags($tag) as $_tag) {
            $valid = $valid && $this->concreteFrontend->isValidTag($_tag);
        }
        
        return $valid;
    }
    
    /**
     * @inheritDoc
     */
    public function prepareIdentifier($entryIdentifier)
    {
        if (! is_string($entryIdentifier) && ! is_numeric($entryIdentifier)) {
            return $entryIdentifier;
        }
        
        $entryIdentifier = Inflector::toFile($entryIdentifier);
        if (strlen($entryIdentifier) <= 128) {
            return $entryIdentifier;
        }
        
        return substr($entryIdentifier, 0, 128 - 32 - 1) . '-' . md5($entryIdentifier);
    }
    
    /**
     * @inheritDoc
     */
    public function remember(callable $callback, ?array $keyArgs = null, array $options = [])
    {
        $options = Options::make($options, $this->getRememberOptions($callback, $keyArgs));
        if ($options['enabled'] === false) {
            return $callback();
        }
        
        $key = $this->getCacheKey($options['keyGenerator'], $options['useEnvironment']);
        
        if ($this->has($key)) {
            $value = $this->get($key);
            
            $value = $this->beforeWarmup($key, $value, $options);
            
            if ($options['onWarmup'] !== null) {
                $value = call_user_func($options['onWarmup'], $value);
            }
            
            return $value;
        }
        
        $tags = empty($options['tags']) ? ($keyArgs ?? []) : $options['tags'];
        
        $lifetime = is_int($options['lifetime']) ? $options['lifetime'] : null;
        $enabled = is_bool($options['enabled']) ? $options['enabled'] : true;
        $value = $this->wrapGeneratorCall($key, function (?int &$lifetime, bool &$enabled) use ($callback, $options) {
            $value = $callback();
            
            if (is_callable($options['enabled'])) {
                $enabled = (bool)call_user_func($options['enabled'], $value, $enabled);
            }
            
            if (is_callable($options['lifetime'])) {
                $_lifetime = call_user_func($options['lifetime'], $value, $lifetime);
                $lifetime = $_lifetime === null ? null : (int)$_lifetime;
                unset($_lifetime);
            }
            
            return $value;
        }, $options, $tags, $lifetime, $enabled);
        
        // Skip, if the the caching was disabled on the fly
        if (! $enabled) {
            return $value;
        }
        
        $frozen = $value;
        if ($options['onFreeze'] !== null) {
            $frozen = call_user_func($options['onFreeze'], $frozen);
        }
        
        $frozen = $this->afterFreeze($key, $frozen, $value, $options, $tags, $lifetime);
        
        $this->set($key, $frozen, $tags, $lifetime);
        
        return $value;
    }
    
    /**
     * @inheritDoc
     */
    public function getCacheKey($keyArgsOrGenerator, ?bool $withEnvironment = null): string
    {
        if ($keyArgsOrGenerator instanceof CacheKeyGeneratorInterface) {
            $key = $keyArgsOrGenerator->makeCacheKey();
        } else {
            $key = GeneralUtility::makeInstance(ArrayBasedCacheKeyGenerator::class,
                is_array($keyArgsOrGenerator) ? $keyArgsOrGenerator : [$keyArgsOrGenerator]);
        }
        
        if ($withEnvironment === null) {
            $withEnvironment = $this->useEnvironment();
        }
        
        return md5(static::class . implode('.', [
                $key,
                $withEnvironment ? $this->environmentCacheKeyGenerator->makeCacheKey() : '-1',
            ]));
    }
    
    /**
     * Hook method for child classes to implement. If this method returns true, the cache is not
     * retrieved but written. This allows to force-refresh the cache
     *
     * @return bool
     * @see remember()
     */
    protected function isUpdate(): bool { return false; }
    
    /**
     * Hook method for child classes to implement. If this method returns true, the
     * cache keys get automatically include environment specific arguments.
     *
     * @return bool
     * @see remember()
     */
    protected function useEnvironment(): bool { return true; }
    
    /**
     * Hook method for child classes to implement. Allows children to wrap the generator
     * with additional code.
     *
     * @param   string    $key
     * @param   \Closure  $generator
     * @param   array     $options
     * @param   array     $tags
     * @param   int|null  $lifetime
     * @param   bool      $enabled
     *
     * @return mixed
     * @see remember()
     */
    protected function wrapGeneratorCall(
        string $key,
        Closure $generator,
        array $options,
        array &$tags,
        ?int &$lifetime,
        bool &$enabled
    )
    {
        return $generator($lifetime, $enabled);
    }
    
    /**
     * Hook method for child classes to implement. Allows your child to modify a value
     * that was retrieved from the cache
     *
     * @param   string  $key
     * @param   mixed   $value
     * @param   array   $options
     *
     * @return mixed
     * @see remember()
     */
    protected function beforeWarmup(string $key, $value, array $options) { return $value; }
    
    /**
     * Hook method for child classes to implement. Allows your child to modify a value
     * right before it will be stored into the cache
     *
     * @param   string    $key
     * @param   mixed     $frozen
     * @param   mixed     $value
     * @param   array     $options
     * @param   array     $tags
     * @param   int|null  $lifetime
     *
     * @return mixed
     */
    protected function afterFreeze(string $key, $frozen, $value, array $options, array &$tags, ?int &$lifetime) { return $frozen; }
    
    /**
     * Hook method to build the option definition for the remember() method.
     * Can be extended by child classes.
     *
     * @param   callable    $callback
     * @param   array|null  $keyArgs
     *
     * @return array[]
     * @see remember()
     */
    protected function getRememberOptions(callable $callback, ?array $keyArgs): array
    {
        return [
            'lifetime' => [
                'type' => ['int', 'null', 'callable'],
                'default' => null,
            ],
            'enabled' => [
                'type' => ['bool', 'callable'],
                'default' => true,
            ],
            'keyGenerator' => [
                'type' => CacheKeyGeneratorInterface::class,
                'default' => function () use ($callback, $keyArgs) {
                    if (is_array($keyArgs)) {
                        return GeneralUtility::makeInstance(ArrayBasedCacheKeyGenerator::class, $keyArgs);
                    }
                    
                    return GeneralUtility::makeInstance(CallableCacheKeyGenerator::class, $callback);
                },
            ],
            'useEnvironment' => [
                'type' => 'bool',
                'default' => $this->useEnvironment(),
            ],
            'tags' => [
                'type' => 'array',
                'default' => [],
            ],
            'onFreeze' => [
                'type' => ['callable', 'null'],
                'default' => null,
            ],
            'onWarmup' => [
                'type' => ['callable', 'null'],
                'default' => null,
            ],
        ];
    }
    
    /**
     * Converts A valid tag into a list of tags.
     *
     * @param   string|array|mixed  $tags
     *
     * @return array
     */
    protected function prepareTags($tags): array
    {
        if ($tags === null) {
            return [];
        }
        
        if (! is_array($tags)) {
            $tags = [$tags];
        }
        
        $filtered = [];
        foreach ($tags as $tag) {
            $filtered[] = CacheUtil::stringifyTag($tag);
        }
        
        return array_merge(...$filtered);
    }
}
