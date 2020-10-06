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


namespace LaborDigital\Typo3BetterApi\Link\LinkBrowser;


use LaborDigital\Typo3BetterApi\Container\ContainerAwareTrait;
use LaborDigital\Typo3BetterApi\Link\LinkService;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use TYPO3\CMS\Frontend\Typolink\AbstractTypolinkBuilder;

class LinkSetRecordLinkBuilder extends AbstractTypolinkBuilder
{
    use ContainerAwareTrait;

    /**
     * @inheritDoc
     */
    public function build(array &$linkDetails, string $linkText, string $target, array $conf): array
    {
        $config = $this->getInstanceOf(TypoContext::class)->Config()->getTsConfigValue(
            'TCEMAIN.linkHandler.' . $linkDetails['identifier'] . '.configuration');

        $link = $this->getInstanceOf(LinkService::class)
                     ->getLink(substr($linkDetails['identifier'], 8), [$config['arg'] => $linkDetails['uid']])->build();

        return [
            $link,
            $linkText,
            $target
                ?: $this->resolveTargetAttribute($conf, 'extTarget', true,
                $this->getTypoScriptFrontendController()->extTarget),
        ];
    }

}
