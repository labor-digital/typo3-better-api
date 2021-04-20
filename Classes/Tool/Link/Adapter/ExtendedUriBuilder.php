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
 * Last modified: 2020.09.03 at 21:30
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

namespace LaborDigital\T3BA\Tool\Link\Adapter;

use LaborDigital\T3BA\Core\Di\PublicServiceInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class ExtendedUriBuilder
 *
 * A wrapper which allows us to manually inject a content object renderer if non is present
 *
 * @package LaborDigital\T3BA\Tool\Link\Adapter
 */
class ExtendedUriBuilder extends UriBuilder implements PublicServiceInterface
{
    public function setContentObject(ContentObjectRenderer $cObject)
    {
        $this->contentObject = $cObject;
    }

    public function hasContentObject(): bool
    {
        return ! empty($this->contentObject);
    }

    public function getContentObject(): ContentObjectRenderer
    {
        return $this->contentObject;
    }
}