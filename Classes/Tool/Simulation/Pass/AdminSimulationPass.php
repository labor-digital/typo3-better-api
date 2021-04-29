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

namespace LaborDigital\T3BA\Tool\Simulation\Pass;


use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Tool\Simulation\AdminUserAuthentication;
use LaborDigital\T3BA\Tool\TypoContext\TypoContextAwareTrait;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\UserAspect;

class AdminSimulationPass implements SimulatorPassInterface
{
    use ContainerAwareTrait;
    use TypoContextAwareTrait;
    
    /**
     * Holds the cached version of the admin user authentication
     *
     * @var AdminUserAuthentication
     */
    protected $adminUserAuth;
    
    /**
     * @inheritDoc
     */
    public function addOptionDefinition(array $options): array
    {
        $options['asAdmin'] = [
            'type' => 'bool',
            'default' => false,
        ];
        
        return $options;
    }
    
    /**
     * @inheritDoc
     */
    public function requireSimulation(array $options, array &$storage): bool
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
    public function setup(array $options, array &$storage): void
    {
        // Backup the data
        $storage['aspect'] = $this->getTypoContext()->getRootContext()->getAspect('backend.user');
        $storage['user'] = $currentUser = $GLOBALS['BE_USER'];
        
        // Make the admin user
        $adminUser = $this->getAdminAuth();
        
        // Create a more speaking log if possible
        if ($currentUser instanceof BackendUserAuthentication
            && is_array($currentUser->user)
            && ! empty($currentUser->user['uid'])) {
            $adminUser = clone $adminUser;
            $adminUser->user['ses_backuserid'] = $currentUser->user['uid'];
        }
        
        // Inject the admin user
        $GLOBALS['BE_USER'] = $adminUser;
        $this->getTypoContext()->getRootContext()->setAspect('backend.user',
            $this->makeInstance(UserAspect::class, [$adminUser])
        );
    }
    
    /**
     * @inheritDoc
     */
    public function rollBack(array $storage): void
    {
        $GLOBALS['BE_USER'] = $storage['user'];
        $this->getTypoContext()->getRootContext()->setAspect('backend.user', $storage['aspect']);
    }
    
    /**
     * Tries to return a cached user auth or creates a new one
     *
     * @return AdminUserAuthentication
     */
    protected function getAdminAuth(): AdminUserAuthentication
    {
        // Check if the auth already exists
        if (isset($this->adminUserAuth)) {
            return $this->adminUserAuth;
        }
        
        // Create a new instance
        $this->adminUserAuth = $this->getService(AdminUserAuthentication::class);
        $this->adminUserAuth->start();
        
        return $this->adminUserAuth;
    }
    
}
