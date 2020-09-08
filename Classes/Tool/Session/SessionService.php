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
 * Last modified: 2020.08.23 at 23:23
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

namespace LaborDigital\T3BA\Tool\Session;

use LaborDigital\T3BA\Core\DependencyInjection\ContainerAwareTrait;
use TYPO3\CMS\Core\SingletonInterface;

class SessionService implements SingletonInterface
{
    use ContainerAwareTrait;

    /**
     * Returns the instance of the frontend session
     *
     * @return SessionInterface
     */
    public function getFrontendSession(): SessionInterface
    {
        return $this->getSingletonOf(FrontendSessionProvider::class);
    }

    /**
     * Returns the instance of the backend session
     *
     * @return SessionInterface
     */
    public function getBackendSession(): SessionInterface
    {
        return $this->getSingletonOf(BackendSessionProvider::class);
    }
}
