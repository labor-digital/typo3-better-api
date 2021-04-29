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
 * Last modified: 2020.10.05 at 22:09
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Link\LinkBrowser;


use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Tool\Link\LinkException;
use LaborDigital\T3BA\Tool\Link\LinkService;
use TYPO3\CMS\Frontend\Typolink\AbstractTypolinkBuilder;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

class LinkBuilder extends AbstractTypolinkBuilder
{
    use ContainerAwareTrait;
    
    /**
     * @inheritDoc
     */
    public function build(array &$linkDetails, string $linkText, string $target, array $conf): array
    {
        $config = $this->cs()->typoContext->Config()->getTsConfigValue(
            'TCEMAIN.linkHandler.' . $linkDetails['identifier'] . '.configuration');
        
        try {
            $link = $this->getService(LinkService::class)
                         ->getLink(substr($linkDetails['identifier'], 8), [$config['arg'] => $linkDetails['uid']])
                         ->build();
        } catch (LinkException $e) {
            throw new UnableToLinkException(
                $e->getMessage(),
                1606838973297,
                null,
                $linkText
            );
        }
        
        return [
            $link,
            $linkText,
            $target
                ?: $this->resolveTargetAttribute($conf, 'target', true,
                $this->getTypoScriptFrontendController()->extTarget),
        ];
    }
    
}
