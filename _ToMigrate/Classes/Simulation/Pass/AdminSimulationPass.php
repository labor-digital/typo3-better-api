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
 * Last modified: 2020.07.17 at 19:13
 */

declare(strict_types=1);


namespace LaborDigital\Typo3BetterApi\Simulation\Pass;


use LaborDigital\Typo3BetterApi\Container\CommonDependencyTrait;
use LaborDigital\Typo3BetterApi\Simulation\AdminUserAuthentication;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\UserAspect;

class AdminSimulationPass implements SimulatorPassInterface
{
    use CommonDependencyTrait;
    
    protected $userBackup;
    protected $aspectBackup;
    
    /**
     * Holds the cached version of the admin user authentication
     *
     * @var \LaborDigital\Typo3BetterApi\Simulation\AdminUserAuthentication
     */
    protected static $adminUserAuth;
    
    /**
     * @inheritDoc
     */
    public function __construct() { }
    
    /**
     * @inheritDoc
     */
    public function addOptionDefinition(array $options): array
    {
        $options['asAdmin'] = [
            'type'    => 'bool',
            'default' => false,
        ];
        
        return $options;
    }
    
    /**
     * @inheritDoc
     */
    public function requireSimulation(array $options): bool
    {
        return $options['asAdmin'] === true
               // Check if we already are logged in as and admin -> If we are, we don't need the simulation
               && (
                   // Simulate if no user
                   ! $GLOBALS['BE_USER'] instanceof BackendUserAuthentication
                   // or no admin
                   || ! $GLOBALS['BE_USER']->isAdmin()
               );
    }
    
    /**
     * @inheritDoc
     */
    public function setup(array $options): void
    {
        // Backup the data
        $this->aspectBackup = $this->TypoContext()->getRootContext()->getAspect('backend.user');
        $this->userBackup   = $currentUser = $GLOBALS['BE_USER'];
        
        // Make the admin user
        $adminUser = $this->getAdminAuth();
        
        // Create a more speaking log if possible
        if ($currentUser instanceof BackendUserAuthentication
            && is_array($currentUser->user)
            && ! empty($currentUser->user['uid'])) {
            $adminUser                         = clone $adminUser;
            $adminUser->user['ses_backuserid'] = $currentUser->user['uid'];
        }
        
        // Inject the admin user
        $GLOBALS['BE_USER'] = $adminUser;
        $this->TypoContext()->getRootContext()->setAspect('backend.user',
            $this->Container()->getWithoutDi(UserAspect::class, [$adminUser])
        );
    }
    
    /**
     * @inheritDoc
     */
    public function rollBack(): void
    {
        $GLOBALS['BE_USER'] = $this->userBackup;
        $this->TypoContext()->getRootContext()->setAspect('backend.user', $this->aspectBackup);
    }
    
    /**
     * Tries to return a cached user auth or creates a new one
     *
     * @return \LaborDigital\Typo3BetterApi\Simulation\AdminUserAuthentication
     */
    protected function getAdminAuth(): AdminUserAuthentication
    {
        // Check if the auth already exists
        if (isset(static::$adminUserAuth)) {
            return static::$adminUserAuth;
        }
        
        // Create a new instance
        static::$adminUserAuth = $this->getInstanceOf(AdminUserAuthentication::class);
        static::$adminUserAuth->start();
        
        return static::$adminUserAuth;
    }
    
}
