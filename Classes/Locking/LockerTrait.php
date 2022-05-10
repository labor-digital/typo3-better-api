<?php
/*
 * Copyright 2022 LABOR.digital
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
 * Last modified: 2022.05.10 at 18:45
 */

declare(strict_types=1);


namespace LaborDigital\Typo3BetterApi\Locking;


use TYPO3\CMS\Core\Locking\Exception\LockAcquireWouldBlockException;
use TYPO3\CMS\Core\Locking\LockFactory;
use TYPO3\CMS\Core\Locking\LockingStrategyInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait LockerTrait
{
    /**
     * Allows the implementation class to define the name of the lock to use
     *
     * @var string|null
     */
    protected $lockKey;

    /**
     * NEVER use this! ALWAYS use getLock() instead!
     *
     * @var LockingStrategyInterface[]
     */
    protected $locks = [];

    /**
     * Generates the internal lock hash based on the given $key, the lockKey and the class name
     *
     * @param   string|null  $key
     *
     * @return string
     */
    protected function getLockKeyHash(?string $key = null): string
    {
        return md5($key . '|' . $this->lockKey . '|' . static::class);
    }

    /**
     * Checks if there is a lock available for the given key
     *
     * @param   string|null  $key
     *
     * @return bool
     */
    protected function hasLock(?string $key = null): bool
    {
        return isset($this->locks[$this->getLockKeyHash($key)]);
    }

    /**
     * Resolves the lock instance to populate the $lock property and returns it.
     *
     * @param   string|null  $key
     *
     * @return \TYPO3\CMS\Core\Locking\LockingStrategyInterface
     */
    protected function getLock(?string $key = null): LockingStrategyInterface
    {
        $localKey = $this->getLockKeyHash($key);

        /** @noinspection NullCoalescingOperatorCanBeUsedInspection */
        if (isset($this->locks[$localKey])) {
            return $this->locks[$localKey];
        }

        return $this->locks[$localKey]
            = GeneralUtility::makeInstance(LockFactory::class)
                            ->createLocker(
                                $localKey,
                                LockingStrategyInterface::LOCK_CAPABILITY_EXCLUSIVE
                                | LockingStrategyInterface::LOCK_CAPABILITY_NOBLOCK
                            );
    }

    /**
     * Tries to acquire the lock for the class or waits up to 10 seconds
     * (with a re-try every 100ms) until the lock has been released.
     *
     * @return bool True if the lock was acquired, false if the lock could not be acquired
     */
    protected function acquireLock(?string $key = null): bool
    {
        $lock = $this->getLock($key);
        for ($i = 0; $i < 100; $i++) {
            try {
                $locked = $lock->acquire(LockingStrategyInterface::LOCK_CAPABILITY_EXCLUSIVE
                                         | LockingStrategyInterface::LOCK_CAPABILITY_NOBLOCK);
            } catch (LockAcquireWouldBlockException $e) {
                // Try again in 100ms
                usleep(100000);
                continue;
            }

            if ($locked) {
                return true;
            }
        }

        return false;
    }

    /**
     * Releases the lock acquired by this trait
     *
     * @param   string|null  $key
     *
     * @return void
     */
    protected function releaseLock(?string $key = null): void
    {
        if (! $this->hasLock($key)) {
            return;
        }

        $lock = $this->getLock($key);
        $lock->release();
        $lock->destroy();
    }

    /**
     * Releases all locks that have been created by this class
     *
     * @return void
     */
    protected function releaseAllLocks(): void
    {
        foreach ($this->locks as $lock) {
            $lock->release();
            $lock->destroy();
        }
    }
}
