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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Configuration\ExtConfig;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\Core\ConfigureTypoCoreInterface;
use LaborDigital\T3ba\ExtConfigHandler\Core\TypoCoreConfigurator;
use LaborDigital\T3ba\ExtConfigHandler\Fluid\ConfigureFluidInterface;
use LaborDigital\T3ba\ExtConfigHandler\Fluid\FluidConfigurator;
use LaborDigital\T3ba\ExtConfigHandler\Raw\ConfigureRawSettingsInterface;
use LaborDigital\T3ba\ExtConfigHandler\Routing\ConfigureRoutingInterface;
use LaborDigital\T3ba\ExtConfigHandler\Routing\RoutingConfigurator;
use LaborDigital\T3ba\FormEngine\Addon\FalFileBaseDir;
use LaborDigital\T3ba\FormEngine\Node\InlineWithNewCeWizardNode;
use LaborDigital\T3ba\FormEngine\UserFunc\FileGenericOverrideChildTcaDataProvider;
use LaborDigital\T3ba\FormEngine\UserFunc\InlineColPosHook;
use LaborDigital\T3ba\FormEngine\UserFunc\InlineContentElementWizardDataProvider;
use LaborDigital\T3ba\Middleware\RequestCollectorMiddleware;
use LaborDigital\T3ba\Middleware\TablePreviewResolverMiddleware;
use LaborDigital\T3ba\Tool\BackendPreview\Hook\Legacy\ItemPreviewRenderer;
use LaborDigital\T3ba\Tool\DataHook\FieldPacker\FlexFormFieldPacker;
use LaborDigital\T3ba\Tool\FormEngine\Custom\Field\CustomFieldNode;
use LaborDigital\T3ba\Tool\FormEngine\Custom\Wizard\CustomWizardNode;
use LaborDigital\T3ba\Tool\Http\Routing\Aspect\StoragePidAwarePersistedAliasMapper;
use LaborDigital\T3ba\Tool\Http\Routing\Aspect\UrlEncodeMapper;
use LaborDigital\T3ba\Tool\Link\LinkBrowser\LinkBuilder;
use LaborDigital\T3ba\Tool\Link\LinkBrowser\LinkHandler;
use LaborDigital\T3ba\Tool\Tca\Preview\PreviewLinkHook;
use Neunerlei\Configuration\State\ConfigState;
use TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowInitializeNew;
use TYPO3\CMS\Backend\Form\FormDataProvider\InitializeProcessedTca;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexPrepare;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaFlexProcess;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineConfiguration;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;

class Core implements ConfigureRawSettingsInterface,
                      ConfigureFluidInterface,
                      ConfigureRoutingInterface,
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
            'SYS' => [
                'linkHandler' => [
                    'linkSetRecord' => LinkHandler::class,
                ],
                'formEngine' => [
                    'linkHandler' => [
                        'linkSetRecord' => LinkHandler::class,
                    ],
                    'nodeRegistry' => [
                        't3baField' => [
                            'nodeName' => 't3baField',
                            'priority' => 40,
                            'class' => CustomFieldNode::class,
                        ],
                        't3baWizard' => [
                            'nodeName' => 't3baWizard',
                            'priority' => 40,
                            'class' => CustomWizardNode::class,
                        ],
                        't3baInlineWithNewCeWizard' => [
                            'nodeName' => 't3baInlineWithNewCeWizard',
                            'priority' => 40,
                            'class' => InlineWithNewCeWizardNode::class,
                        ],
                    ],
                    'formDataGroup' => [
                        'tcaDatabaseRecord' => [
                            InlineContentElementWizardDataProvider::class => [
                                'before' => [DatabaseRowInitializeNew::class],
                                'depends' => [InitializeProcessedTca::class],
                            ],
                            FileGenericOverrideChildTcaDataProvider::class => [
                                'before' => [TcaInlineConfiguration::class, TcaFlexProcess::class],
                                'depends' => [TcaFlexPrepare::class],
                            ],
                        ],
                    ],
                ],
            ],
            'FE' => [
                'typolinkBuilder' => [
                    'linkSetRecord' => LinkBuilder::class,
                ],
            ],
            'SC_OPTIONS' => [
                't3lib/class.t3lib_befunc.php' => [
                    'viewOnClickClass' => [
                        PreviewLinkHook::class => PreviewLinkHook::class,
                    ],
                ],
                
                't3lib/class.t3lib_userauthgroup.php' => [
                    'getDefaultUploadFolder' => [
                        FalFileBaseDir::class => FalFileBaseDir::class . '->applyConfiguredFalFolders',
                    ],
                ],
                
                'cms/layout/class.tx_cms_layout.php' => [
                    // Configuration to support legacy backend preview renderering which is used for gridelements
                    'tt_content_drawItem' => [
                        ItemPreviewRenderer::class => ItemPreviewRenderer::class,
                    ],
                    
                    'record_is_used' => [
                        InlineColPosHook::class => InlineColPosHook::class . '->isContentUsed',
                    ],
                ],
                
                'typo3/class.db_list_extra.inc' => [
                    'getTable' => [
                        InlineColPosHook::class => InlineColPosHook::class,
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
            'groups' => 'pages',
            'options' => [
                'compression' => true,
            ],
        ]);
        $configurator->registerCache('t3ba_system', VariableFrontend::class, Typo3DatabaseBackend::class, [
            'groups' => 'system',
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
    public static function configureRouting(RoutingConfigurator $configurator, ExtConfigContext $context): void
    {
        $configurator
            ->registerRouteAspectHandler(
                'T3BAStoragePidAwarePersistedAliasMapper',
                StoragePidAwarePersistedAliasMapper::class
            )
            ->registerRouteAspectHandler(
                'T3BAUrlEncodeMapper',
                UrlEncodeMapper::class
            );
        
        $configurator
            ->registerMiddleware(TablePreviewResolverMiddleware::class, [
                'before' => 'typo3/cms-frontend/site',
            ])
            ->registerMiddleware(RequestCollectorMiddleware::class, [
                'after' => 'typo3/cms-frontend/site',
                'before' => 'typo3/cms-frontend/base-redirect-resolver',
            ])
            ->registerMiddleware(RequestCollectorMiddleware::class, [
                'stack' => 'backend',
                'after' => 'typo3/cms-backend/site-resolver',
            ]);
    }
}
