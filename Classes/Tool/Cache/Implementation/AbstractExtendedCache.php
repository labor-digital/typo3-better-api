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
 * Last modified: 2021.04.29 at 16:29
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Cache\Implementation;


use LaborDigital\T3BA\Tool\Cache\CacheInterface;
use LaborDigital\T3BA\Tool\Cache\InvalidArgumentException;
use LaborDigital\T3BA\Tool\Cache\KeyGenerator\ArrayBasedCacheKeyGenerator;
use LaborDigital\T3BA\Tool\Cache\KeyGenerator\CacheKeyGeneratorInterface;
use LaborDigital\T3BA\Tool\Cache\KeyGenerator\CallableCacheKeyGenerator;
use LaborDigital\T3BA\Tool\Cache\Util\CacheUtil;
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
     * @var \LaborDigital\T3BA\Tool\Cache\KeyGenerator\CacheKeyGeneratorInterface
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
    ) {
        $this->concreteFrontend             = $concreteFrontend;
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
     * Returns the TYPO3 cache object that gets wrapped by this instance
     *
     * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
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
     * Helper to make sure the cache key is no longer than 128 characters
     *
     * @param   mixed  $entryIdentifier
     *
     * @return string|mixed
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
     * The given $callback is called once and then cached. All subsequent calls
     * will then first try to serve the cached value instead of calling $callback again.
     *
     * The execution of remember() can be nested in order to build cached data trees.
     * This also means that outer executions will inherit the cache options like ttl, tags and "enabled" state
     * from the inner executions.
     *
     * @param   callable    $callback  The callable to generate the value to be cached
     * @param   array|null  $keyArgs   Allows you to pass key arguments to generate the cache key with
     *                                 You can omit this parameter if you are supplying your own keyGenerator
     *                                 implementation in the options
     * @param   array       $options   Additional options
     *                                 - ttl int|callable: The numeric value in seconds for how long the cache entry
     *                                 should be stored. Can be a callable which receives the $callback result,
     *                                 to create a ttl based on the output. Is inherited to outer scopes.
     *                                 - enabled bool|callable (true): Allows you to dynamically disable the cache
     *                                 for this execution. Can be a callable which receives the $callback result,
     *                                 to enable/disable the cache based on the output. Is inherited to outer scopes.
     *                                 - keyGenerator CacheKeyGeneratorInterface: The generator instance
     *                                 which is used to generate a cache key for this entry.
     *                                 - useEnvironment bool: Determines if the environment should be taken into account
     *                                 when a cache key is generated. The "environment" is the current language,
     *                                 the current site(not page!), user groups, mount point or page types.
     *                                 The flag is automatically set depending by your implementation.
     *                                 - tags array: A list of tags that should be added to this cache entry.
     *                                 The tags will be inherited to outer scopes.
     *                                 - onFreeze callable: A callback to execute before the result of $callback is
     *                                 written into the cache. Allows you to perform additional post processing on the
     *                                 fly. The callback receives the result as parameter.
     *                                 - onWarmup callable: A callback to execute when the cached value is read from
     *                                 the caching system. Allows you to rehydrate objects on the fly. The callback
     *                                 receives the value as parameter.
     *
     * @return false|mixed
     */
    public function remember(callable $callback, ?array $keyArgs = null, array $options = [])
    {
        $options = Options::make($options, $this->getRememberOptions($callback, $keyArgs));

        if ($options['enabled'] === false) {
            return $callback();
        }

        $identifier = $this->getCacheKey($options['keyGenerator'], $options['useEnvironment']);

        if ($this->has($identifier)) {
            $value = $this->get($identifier);

            $value = $this->beforeWarmup($value, $options);

            if ($options['onWarmup'] !== null) {
                $value = call_user_func($options['onWarmup'], $value);
            }

            return $value;
        }

        $tags    = empty($options['tags']) ? $this->prepareTags($keyArgs) : $options['tags'];
        $ttl     = null;
        $enabled = true;
        $value   = $this->wrapGeneratorCall(function (?int &$ttl, bool &$enabled) use ($callback, $options) {
            $value = $callback();

            if (is_bool($options['enabled'])) {
                $enabled = $options['enabled'];
            } elseif (is_callable($options['enabled'])) {
                $enabled = (bool)call_user_func($options['enabled'], $value);
            }

            if (is_int($options['ttl'])) {
                $ttl = $options['ttl'];
            } elseif (is_callable($options['ttl'])) {
                $_ttl = call_user_func($options['ttl'], $value);
                $ttl  = $_ttl === null ? null : (int)$_ttl;
                unset($_ttl);
            }

            return $value;
        }, $options, $tags, $ttl, $enabled);

        // Skip, if the the caching was disabled on the fly
        if (! $enabled) {
            return $value;
        }

        $frozen = $value;
        if ($options['onFreeze'] !== null) {
            $frozen = call_user_func($options['onFreeze'], $frozen);
        }

        $frozen = $this->afterFreeze($frozen, $value, $options, $tags, $ttl);

        $this->set($identifier, $frozen, $tags, $ttl);

        return $value;
    }

    /**
     * Generates a cache key, the same way remember() does.
     *
     * @param   array|CacheKeyGeneratorInterface  $keyArgsOrGenerator  Either a cache key generator, or a list of
     *                                                                 arguments that should be converted into a key
     * @param   bool                              $withEnvironment     By default the environment will be taken into
     *                                                                 account. If you set this to false, only the key
     *                                                                 generator is used.
     *
     * @return string
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
    protected function useEnvironment(): bool { return false; }

    /**
     * Hook method for child classes to implement. Allows children to wrap the generator
     * with additional code.
     *
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
        \Closure $generator,
        array $options,
        array &$tags,
        ?int &$lifetime,
        bool &$enabled
    ) {
        return $generator($lifetime, $enabled);
    }

    /**
     * Hook method for child classes to implement. Allows your child to modify a value
     * that was retrieved from the cache
     *
     * @param   mixed  $value
     * @param   array  $options
     *
     * @return mixed
     * @see remember()
     */
    protected function beforeWarmup($value, array $options) { return $value; }

    /**
     * Hook method for child classes to implement. Allows your child to modify a value
     * right before it will be stored into the cache
     *
     * @param   mixed     $frozen
     * @param   mixed     $value
     * @param   array     $options
     * @param   array     $tags
     * @param   int|null  $lifetime
     *
     * @return mixed
     */
    protected function afterFreeze($frozen, $value, array $options, array &$tags, ?int &$lifetime) { return $frozen; }

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
            'lifetime'       => [
                'type'    => ['int', 'null', 'callable'],
                'default' => null,
            ],
            'enabled'        => [
                'type'    => ['bool', 'callable'],
                'default' => true,
            ],
            'keyGenerator'   => [
                'type'    => CacheKeyGeneratorInterface::class,
                'default' => function () use ($callback, $keyArgs) {
                    if (is_array($keyArgs)) {
                        return GeneralUtility::makeInstance(ArrayBasedCacheKeyGenerator::class, $keyArgs);
                    }

                    return GeneralUtility::makeInstance(CallableCacheKeyGenerator::class, $callback);
                },
            ],
            'useEnvironment' => [
                'type'    => 'bool',
                'default' => $this->useEnvironment(),
            ],
            'tags'           => [
                'type'    => 'array',
                'default' => [],
            ],
            'onFreeze'       => [
                'type'    => ['callable', 'null'],
                'default' => null,
            ],
            'onWarmup'       => [
                'type'    => ['callable', 'null'],
                'default' => null,
            ],
        ];
    }

    /**
     * Converts A valid tag into a list of tags.
     *
     * @param   string|mixed[]|mixed  $tags
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
