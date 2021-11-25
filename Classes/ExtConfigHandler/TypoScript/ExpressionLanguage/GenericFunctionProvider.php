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
 * Last modified: 2021.11.23 at 10:47
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\TypoScript\ExpressionLanguage;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;
use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;

/**
 * Adapter to register function only providers without creating an additional "wrapper" to
 * register the actual expressionLanguageProviders
 */
class GenericFunctionProvider extends AbstractProvider implements NoDiInterface
{
    use TypoContextAwareTrait;
    
    /**
     * @inheritDoc
     */
    public function getExpressionLanguageProviders(): array
    {
        $config = $this->getTypoContext()->config()->getConfigValue('typo.typoScript.expressionLanguage.functionProviders', []);
        
        return is_array($config) ? $config : [];
    }
    
}