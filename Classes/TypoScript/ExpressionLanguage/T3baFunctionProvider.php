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
 * Last modified: 2021.11.23 at 10:38
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\TypoScript\ExpressionLanguage;


use LaborDigital\T3ba\Tool\TypoContext\StaticTypoContextAwareTrait;
use Neunerlei\Inflection\Inflector;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class T3baFunctionProvider implements ExpressionFunctionProviderInterface
{
    use StaticTypoContextAwareTrait;
    
    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            $this->getBetterSiteFunction(),
        ];
    }
    
    public function getBetterSiteFunction(): ExpressionFunction
    {
        return new ExpressionFunction('betterSite', static function () { },
            static function ($_, $valueKey = null, $siteIdentifier = null) {
                $siteFacet = static::getTypoContext()->site();
                $site = $siteIdentifier ? $siteFacet->get((string)$siteIdentifier) : $siteFacet->getCurrent();
                $getter = Inflector::toGetter($valueKey ? (string)$valueKey : 'identifier');
                
                return $site->$getter();
            });
    }
}