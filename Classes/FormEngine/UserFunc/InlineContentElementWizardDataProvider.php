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
 * Last modified: 2021.07.12 at 11:57
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\FormEngine\UserFunc;


use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;

class InlineContentElementWizardDataProvider implements FormDataProviderInterface
{
    use TypoContextAwareTrait;
    
    public const AJAX_TARGET = 'T3BA-InlineWithNewCeWizardNode';
    
    public static $forbiddenDefVals = ['tx_gridelements_columns'];
    
    /**
     * @inheritDoc
     */
    public function addData(array $result)
    {
        if ($result['command'] !== 'new' || $result['isInlineChild'] !== true) {
            return $result;
        }
        
        $ajax = $this->getTypoContext()->request()->getPost('ajax');
        if (! is_array($ajax) || ! in_array(static::AJAX_TARGET, $ajax, true)) {
            return $result;
        }
        
        $params = SerializerUtil::unserializeJson((string)($ajax[3] ?? 'null'));
        if (! is_string($params)) {
            return $result;
        }
        
        parse_str($params, $query);
        $defVals = $query['defVals']['tt_content'] ?? [];
        if (! is_array($defVals)) {
            $defVals = [];
        }
        
        foreach (static::$forbiddenDefVals as $forbiddenDefVal) {
            unset($defVals[$forbiddenDefVal]);
        }
        
        $result['defaultValues']['tt_content']
            = array_merge($result['defaultValues']['tt_content'] ?? [], $defVals);
        
        return $result;
    }
    
}