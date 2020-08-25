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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\TypoContext\Aspect;

use LaborDigital\Typo3BetterApi\TypoContext\TypoContextException;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class FeUserAspect extends AbstractBetterUserAspect
{
    
    /**
     * Returns the frontend user authentication object
     *
     * @return \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     * @throws \LaborDigital\Typo3BetterApi\TypoContext\TypoContextException
     */
    public function getUser(): FrontendUserAuthentication
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
        return 'frontend.user';
    }
}
