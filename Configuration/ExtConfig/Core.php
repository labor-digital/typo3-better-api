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
 * Last modified: 2020.10.18 at 20:46
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Configuration\ExtConfig;


use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfigHandler\Core\ConfigureTypoCoreInterface;
use LaborDigital\T3BA\ExtConfigHandler\Core\TypoCoreConfigurator;
use LaborDigital\T3BA\ExtConfigHandler\Fluid\ConfigureFluidInterface;
use LaborDigital\T3BA\ExtConfigHandler\Fluid\FluidConfigurator;
use LaborDigital\T3BA\ExtConfigHandler\Http\ConfigureHttpInterface;
use LaborDigital\T3BA\ExtConfigHandler\Http\HttpConfigurator;
use LaborDigital\T3BA\ExtConfigHandler\Raw\ConfigureRawSettingsInterface;
use LaborDigital\T3BA\FormEngine\Addon\FalFileBaseDir;
use LaborDigital\T3BA\Middleware\RequestCollectorMiddleware;
use LaborDigital\T3BA\Tool\DataHook\FieldPacker\FlexFormFieldPacker;
use LaborDigital\T3BA\Tool\FormEngine\Custom\Field\CustomFieldNode;
use LaborDigital\T3BA\Tool\FormEngine\Custom\Wizard\CustomWizardNode;
use LaborDigital\T3BA\Tool\Http\Routing\Aspect\StoragePidAwarePersistedAliasMapper;
use LaborDigital\T3BA\Tool\Link\LinkBrowser\LinkBuilder;
use LaborDigital\T3BA\Tool\Link\LinkBrowser\LinkHandler;
use Neunerlei\Configuration\State\ConfigState;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;

class Core implements ConfigureRawSettingsInterface, ConfigureFluidInterface, ConfigureHttpInterface,
                      ConfigureTypoCoreInterface
{

    /**
     * @inheritDoc
     */
    public static function configureRaw(ConfigState $state, ExtConfigContext $context): void
    {
        // Register the flex form field packer
        $state->mergeIntoArray('t3ba', [
            'dataHook' => [
                'fieldPackers' => [
                    FlexFormFieldPacker::class,
                ],
            ],
        ]);

        // Register globals configuration for the TYPO3 core api
        $state->mergeIntoArray('typo.globals.TYPO3_CONF_VARS', [
            'SYS'        => [
                'linkHandler' => [
                    'linkSetRecord' => LinkHandler::class,
                ],
                'formEngine'  => [
                    'linkHandler'  => [
                        'linkSetRecord' => LinkHandler::class,
                    ],
                    'nodeRegistry' => [
                        't3baField'  => [
                            'nodeName' => 't3baField',
                            'priority' => 40,
                            'class'    => CustomFieldNode::class,
                        ],
                        't3baWizard' => [
                            'nodeName' => 't3baWizard',
                            'priority' => 40,
                            'class'    => CustomWizardNode::class,
                        ],
                    ],
                ],
            ],
            'FE'         => [
                'typolinkBuilder' => [
                    'linkSetRecord' => LinkBuilder::class,
                ],
            ],
            'SC_OPTIONS' => [
                't3lib/class.t3lib_userauthgroup.php' => [
                    'getDefaultUploadFolder' => [
                        FalFileBaseDir::class => FalFileBaseDir::class . '->applyConfiguredFalFolders',
                    ],
                ],
            ],
        ]);


    }

    /**
     * @inheritDoc
     */
    public static function configureCore(TypoCoreConfigurator $configurator, ExtConfigContext $context): void
    {
        // Register our cache service cache
        $configurator->registerCache('t3ba_frontend', VariableFrontend::class, Typo3DatabaseBackend::class, [
            'groups'  => 'pages',
            'options' => [
                'compression' => true,
            ],
        ]);
        $configurator->registerCache('t3ba_system', VariableFrontend::class, Typo3DatabaseBackend::class, [
            'groups'  => 'system',
            'options' => [
                'compression' => true,
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function configureFluid(FluidConfigurator $configurator, ExtConfigContext $context): void
    {
        $configurator->registerViewHelpers();
    }

    /**
     * @inheritDoc
     */
    public static function configureHttp(HttpConfigurator $configurator, ExtConfigContext $context): void
    {
        $configurator->registerRouteAspectHandler(
            'T3BAStoragePidAwarePersistedAliasMapper',
            StoragePidAwarePersistedAliasMapper::class
        );

        $configurator
            ->registerMiddleware(RequestCollectorMiddleware::class, [
                'after'  => 'typo3/cms-frontend/site',
                'before' => 'typo3/cms-frontend/base-redirect-resolver',
            ])
            ->registerMiddleware(RequestCollectorMiddleware::class, [
                'stack' => 'backend',
                'after' => 'typo3/cms-backend/site-resolver',
            ]);
    }
}
