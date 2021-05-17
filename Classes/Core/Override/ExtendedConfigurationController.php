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
 * Last modified: 2021.05.17 at 17:32
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Core\Override;


use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Event\Backend\LowLevelControllerConfigFilterEvent;
use LaborDigital\T3ba\Tool\Translation\Translator;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Lowlevel\Controller\T3BaCopyConfigurationController;

/**
 * Class ExtendedConfigurationController
 *
 * Extends the configuration controller of the lowLevel extension, to allow registration of additional
 * data sets into the dropdown list
 *
 * @package LaborDigital\T3ba\Core\Override
 */
class ExtendedConfigurationController extends T3BaCopyConfigurationController
{
    /**
     * @inheritDoc
     */
    public function mainAction(ServerRequestInterface $request): ResponseInterface
    {
        $e = TypoEventBus::getInstance()->dispatch(
            new LowLevelControllerConfigFilterEvent()
        );
        
        foreach ($e->getRegisteredData() as $id => $data) {
            $this->treeSetup[$id] = $data['config'];
            $GLOBALS[$data['config']['globalKey']] = $data['data'];
        }
        
        if (! empty($e->getRegisteredLabels())) {
            $translator = TypoContext::getInstance()->di()->getService(Translator::class);
            foreach ($e->getRegisteredLabels() as $original => $override) {
                $translator->registerOverride(
                    'LLL:EXT:lowlevel/Resources/Private/Language/locallang.xlf:' . $original,
                    $override);
            }
        }
        
        return parent::mainAction($request);
    }
    
}