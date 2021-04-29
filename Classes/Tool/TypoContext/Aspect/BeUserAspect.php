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
 * Last modified: 2021.04.29 at 22:17
 */

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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\T3BA\Tool\TypoContext\Aspect;

use LaborDigital\T3BA\Core\Di\PublicServiceInterface;
use LaborDigital\T3BA\Tool\TypoContext\TypoContextException;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class BeUserAspect extends AbstractBetterUserAspect implements PublicServiceInterface
{
    
    /**
     * Returns the frontend user authentication object
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     * @throws \LaborDigital\T3BA\Tool\TypoContext\TypoContextException
     */
    public function getUser(): BackendUserAuthentication
    {
        $user = $this->getUserObject();
        if (empty($user)) {
            throw new TypoContextException('Could not find a user object! Seems like you are to early in the lifecycle');
        }
        
        return $user;
    }
    
    /**
     * @inheritDoc
     */
    protected function getRootAspectKey(): string
    {
        return 'backend.user';
    }
    
    /**
     * @inheritDoc
     */
    protected function getUserObject()
    {
        if (! empty($this->resolvedUser)) {
            return $this->resolvedUser;
        }
        $user = parent::getUserObject();
        if (! empty($user)) {
            return $user;
        }
        if (! empty($GLOBALS['BE_USER'])) {
            return $this->resolvedUser = $GLOBALS['BE_USER'];
        }
        
        return $user;
    }
}
