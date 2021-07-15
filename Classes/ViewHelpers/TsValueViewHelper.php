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
 * Last modified: 2021.07.02 at 10:06
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ViewHelpers;


use LaborDigital\T3ba\Tool\TypoScript\TypoScriptService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class TsValueViewHelper
 *
 * ViewHelper to retrieve the value stored behind a typoScript config path
 *
 * @package LaborDigital\T3ba\ViewHelpers
 */
class TsValueViewHelper extends AbstractViewHelper implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    /**
     * @var \LaborDigital\T3ba\Tool\TypoScript\TypoScriptService
     */
    protected $typoScriptService;
    
    public function __construct(TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
    }
    
    public function initializeArguments(): void
    {
        $this->registerArgument('path', 'string', 'The path to the typoScript property to read');
        $this->registerArgument('removeDots', 'boolean', 'Set to true if the tailing dots should be removed from nested keys', false, true);
        $this->registerArgument('pid', 'integer', 'An optional pid to query the typoScript for');
        $this->registerArgument('default', 'mixed', 'Default value to return if the value was not found', false, null);
    }
    
    public function render()
    {
        try {
            $options = [
                'default' => $this->arguments['default'],
            ];
            
            if (! empty($this->arguments['pid'])) {
                $options['pid'] = $this->arguments['pid'];
            }
            
            $value = $this->typoScriptService->get($this->arguments['path'], $options);
            
            if ($this->arguments['removeDots']) {
                $value = $this->typoScriptService->removeDots($value);
            }
            
            return $value;
        } catch (Throwable $e) {
            $this->logger->warning('Failed to load typoscript value: ' . $this->arguments['path'], ['exception' => $e]);
            
            return $this->arguments['default'];
        }
    }
}