<?php
declare(strict_types=1);
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
 * Last modified: 2020.03.20 at 13:57
 */

namespace LaborDigital\T3BA\Tool\Simulation;


use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;
use LaborDigital\T3BA\Core\Exception\BetterApiException;
use LaborDigital\T3BA\Tool\Database\DbService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;

class AdminUserAuthentication extends BackendUserAuthentication implements PublicServiceInterface
{
    public const ADMIN_USERNAME = '_betterApi_adminUser_';

    /**
     * @var \LaborDigital\T3BA\Tool\Database\DbService
     */
    protected $dbService;

    /**
     * @var \TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory
     */
    protected $passwordHashFactory;

    /**
     * @inheritDoc
     */
    public function __construct(DbService $db, PasswordHashFactory $passwordHashFactory)
    {
        $this->dontSetCookie = true;
        parent::__construct();
        $this->dbService           = $db;
        $this->passwordHashFactory = $passwordHashFactory;
    }

    /**
     * @inheritDoc
     */
    public function start(): void
    {
        parent::start();
        $this->loginUser();
    }

    /**
     * @inheritDoc
     */
    public function backendCheckLogin($proceedIfNoUserIsLoggedIn = false): void
    {
        $this->loginUser();
    }

    /**
     * @inheritDoc
     */
    protected function isUserAllowedToLogin(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function getCookie($cookieName): string
    {
        return '';
    }

    /**
     * @throws \LaborDigital\T3BA\Core\Exception\BetterApiException
     */
    protected function loginUser(): void
    {
        // Skip if we already are logged in
        if (! empty($this->user['uid'])) {
            return;
        }

        // Try to login with a username
        $this->setBeUserByName(static::ADMIN_USERNAME);

        // Check if the login succeeded
        if (empty($this->user['uid'])) {
            $this->ensureBackendUserExists();

            // Try to login again
            $this->setBeUserByName(static::ADMIN_USERNAME);

            // Failed ?
            /** @noinspection NotOptimalIfConditionsInspection */
            if (empty($this->user['uid'])) {
                throw new BetterApiException('Could not automatically create an admin user for you to use!');
            }
        }

        // Initialize the object
        $this->fetchGroupData();
        $this->backendSetUC();
        $this->uc['recursiveDelete'] = true;
    }

    /**
     * Removes all users that match our username from the database and creates a new, admin user
     */
    protected function ensureBackendUserExists(): void
    {
        // Make sure that there are no other remnants of this user...
        $this->dbService->getQuery('be_users', true)
                        ->withWhere(['username' => static::ADMIN_USERNAME])->delete();

        // Create a new user
        $this->dbService->getQuery('be_users', true)
                        ->insert([
                            'username' => static::ADMIN_USERNAME,
                            'password' => $this->generateHashedPassword(),
                            'admin'    => 1,
                            'tstamp'   => $GLOBALS['EXEC_TIME'],
                            'crdate'   => $GLOBALS['EXEC_TIME'],
                        ]);
    }

    /**
     * This function returns a salted hashed key.
     *
     * @return string a random password
     */
    protected function generateHashedPassword(): string
    {
        return $this->passwordHashFactory->getDefaultHashInstance('BE')->getHashedPassword(random_bytes(20));
    }
}
